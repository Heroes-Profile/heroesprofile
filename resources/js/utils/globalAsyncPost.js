const DEFAULT_MAX_ATTEMPTS = 180;
const DEFAULT_POLL_INTERVAL_MS = 2000;

export async function globalAsyncPost(axios, url, data, options = {}) {
  const response = await axios.post(url, data, options);

  if (response.status === 202 || response.data?.async) {
    return pollGlobalAsyncStatus(axios, response.data.job_id, options);
  }

  return response;
}

async function pollGlobalAsyncStatus(
  axios,
  jobId,
  options = {},
  maxAttempts = DEFAULT_MAX_ATTEMPTS,
  intervalMs = DEFAULT_POLL_INTERVAL_MS
) {
  if (!jobId) {
    throw new Error('Missing async job id.');
  }

  for (let attempt = 0; attempt < maxAttempts; attempt++) {
    await new Promise((resolve) => setTimeout(resolve, intervalMs));

    const response = await axios.get(`/api/v1/global/async/status/${jobId}`, options);

    if (response.status === 200 && !response.data?.async) {
      return response;
    }

    if (response.data?.status === 'failed') {
      throw new Error(response.data.error || 'Global query failed.');
    }

    if (response.data?.status === 'not_found') {
      throw new Error('Global query job not found.');
    }
  }

  throw new Error('Timed out waiting for global query results.');
}
