const DEFAULT_MAX_ATTEMPTS = 180;
const DEFAULT_POLL_INTERVAL_MS = 2000;

export function createLoadMeta() {
  return {
    phase: 'idle',
    postUrl: null,
    postStatus: null,
    postHeaders: null,
    postError: null,
    jobId: null,
    pollAttempt: 0,
    pollStatus: null,
    pollError: null,
    mode: null,
    startedAt: null,
    finishedAt: null,
    durationMs: null,
    error: null,
  };
}

function formatAxiosError(error) {
  if (error.response) {
    const status = error.response.status;
    const data = error.response.data;
    const message = typeof data === 'object' ? (data.message || data.error || JSON.stringify(data)) : String(data);

    return `HTTP ${status}: ${message}`;
  }

  if (error.code === 'ERR_CANCELED' || error.message?.includes('canceled')) {
    return 'Request canceled';
  }

  return error.message || String(error);
}

function extractDebugHeaders(headers) {
  if (!headers) {
    return null;
  }

  return {
    cacheStatus: headers['x-global-cache-status'] || null,
    asyncMode: headers['x-global-async-mode'] || null,
  };
}

export function formatLoadMeta(meta) {
  if (!meta) {
    return '';
  }

  const lines = [
    `phase: ${meta.phase}`,
    meta.mode ? `mode: ${meta.mode}` : null,
    meta.postUrl ? `POST: ${meta.postUrl}` : null,
    meta.postStatus ? `POST status: ${meta.postStatus}` : null,
    meta.postHeaders?.asyncMode ? `POST X-Global-Async-Mode: ${meta.postHeaders.asyncMode}` : null,
    meta.postHeaders?.cacheStatus ? `POST X-Global-Cache-Status: ${meta.postHeaders.cacheStatus}` : null,
    meta.jobId ? `job_id: ${meta.jobId}` : null,
    meta.pollAttempt ? `poll attempts: ${meta.pollAttempt}` : null,
    meta.pollStatus ? `poll status: ${meta.pollStatus}` : null,
    meta.durationMs != null ? `duration: ${(meta.durationMs / 1000).toFixed(1)}s` : null,
    meta.error ? `error: ${meta.error}` : null,
  ].filter(Boolean);

  return lines.join(' | ');
}

export async function globalAsyncPost(axios, url, data, options = {}) {
  const { onLoadStatus, loadMeta: externalMeta, ...axiosOptions } = options;
  const meta = externalMeta || createLoadMeta();
  const startedAt = Date.now();
  meta.phase = 'posting';
  meta.postUrl = url;
  meta.startedAt = new Date().toISOString();

  const notify = (message) => {
    meta.durationMs = Date.now() - startedAt;
    if (onLoadStatus) {
      onLoadStatus(message || formatLoadMeta(meta), meta);
    }
  };

  notify('POST sent — waiting for response...');

  try {
    const response = await axios.post(url, data, axiosOptions);
    meta.postStatus = response.status;
    meta.postHeaders = extractDebugHeaders(response.headers);

    if (response.status === 202 || response.data?.async) {
      meta.phase = 'polling';
      meta.mode = 'async-polled';
      meta.jobId = response.data?.job_id || null;
      notify(`POST returned ${response.status} (job_id: ${meta.jobId}) — polling...`);

      const polled = await pollGlobalAsyncStatus(axios, meta.jobId, axiosOptions, meta, notify);

      meta.phase = 'complete';
      meta.finishedAt = new Date().toISOString();
      meta.durationMs = Date.now() - startedAt;
      notify(`Complete after ${meta.pollAttempt} poll(s)`);

      return polled;
    }

    meta.phase = 'complete';
    meta.mode = meta.postHeaders?.cacheStatus === 'fresh' ? 'cache-fresh' : 'direct-sync';
    meta.finishedAt = new Date().toISOString();
    meta.durationMs = Date.now() - startedAt;
    notify(`POST returned ${response.status} (${meta.mode})`);

    return response;
  } catch (error) {
    meta.phase = 'error';
    meta.postError = formatAxiosError(error);
    meta.error = meta.postError;
    meta.finishedAt = new Date().toISOString();
    meta.durationMs = Date.now() - startedAt;
    notify(`Error: ${meta.error}`);
    throw error;
  }
}

async function pollGlobalAsyncStatus(
  axios,
  jobId,
  options = {},
  meta = null,
  notify = null,
  maxAttempts = DEFAULT_MAX_ATTEMPTS,
  intervalMs = DEFAULT_POLL_INTERVAL_MS
) {
  if (!jobId) {
    throw new Error('Missing async job id.');
  }

  for (let attempt = 0; attempt < maxAttempts; attempt++) {
    await new Promise((resolve) => setTimeout(resolve, intervalMs));

    if (meta) {
      meta.pollAttempt = attempt + 1;
    }

    try {
      if (notify) {
        notify(`Polling job ${jobId} — attempt ${attempt + 1}...`);
      }

      const response = await axios.get(`/api/v1/global/status/${jobId}`, options);

      if (meta) {
        meta.pollStatus = response.data?.status || (response.status === 200 ? 'complete' : null);
      }

      if (response.status === 200 && !response.data?.async) {
        return response;
      }

      if (response.data?.status === 'failed') {
        throw new Error(response.data.error || 'Global query failed.');
      }

      if (response.data?.status === 'not_found') {
        throw new Error('Global query job not found.');
      }
    } catch (error) {
      const httpStatus = error.response?.status ?? null;

      if (meta) {
        meta.pollError = formatAxiosError(error);
      }

      if (httpStatus === 524 || error.code === 'ECONNABORTED' || !httpStatus) {
        continue;
      }

      throw error;
    }
  }

  throw new Error('Timed out waiting for global query results.');
}
