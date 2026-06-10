# Google Cloud Tasks — Global Query Setup

One-time GCP configuration for async global stats (Global Hero pilot).

Project: **heroesprofile-244413**

```bash
gcloud config set project heroesprofile-244413
```

## 1. Create the queue

```bash
gcloud tasks queues create global-queries --location=us-east1 --max-attempts=3 --max-retry-duration=3600s
```

## 2. IAM — allow Cloud Run to enqueue tasks

Service account: `heroesprofile-website@heroesprofile-244413.iam.gserviceaccount.com`

```cmd
gcloud projects add-iam-policy-binding heroesprofile-244413 --member=serviceAccount:heroesprofile-website@heroesprofile-244413.iam.gserviceaccount.com --role=roles/cloudtasks.enqueuer --condition=None
```

## 3. Cloud Run service account for task OIDC

Cloud Tasks delivers HTTP tasks with an OIDC token.

```cmd
gcloud iam service-accounts add-iam-policy-binding heroesprofile-website@heroesprofile-244413.iam.gserviceaccount.com --member=serviceAccount:service-484440392946@gcp-sa-cloudtasks.iam.gserviceaccount.com --role=roles/iam.serviceAccountUser --condition=None
```

## 4. Allow Cloud Tasks to invoke Cloud Run (direct URL)

Use the **direct Cloud Run URL**, not `develop.heroesprofile.com`. That bypasses Cloudflare and the load balancer (avoids 120s timeout and “Cloudflare-only” firewall rules). Users still browse via Cloudflare as normal.

Develop direct URL: `https://heroesprofile-website-dev-rsfk4hfj3a-ue.a.run.app`

```cmd
gcloud run services add-iam-policy-binding heroesprofile-website-dev --region=us-east1 --member=serviceAccount:heroesprofile-website@heroesprofile-244413.iam.gserviceaccount.com --role=roles/run.invoker
```

## 5. Cloud Run env vars

**Develop (`heroesprofile-website-dev`):**

```cmd
gcloud run services update heroesprofile-website-dev --region=us-east1 --timeout=3600 --no-cpu-throttling --update-env-vars="GLOBAL_ASYNC_ENABLED=true,CLOUD_TASKS_PROJECT_ID=heroesprofile-244413,CLOUD_TASKS_LOCATION=us-east1,CLOUD_TASKS_QUEUE=global-queries,CLOUD_TASKS_HANDLER_URL=https://heroesprofile-website-dev-rsfk4hfj3a-ue.a.run.app/api/v1/internal/global/process,CLOUD_TASKS_SERVICE_ACCOUNT=heroesprofile-website@heroesprofile-244413.iam.gserviceaccount.com,PHP_MAX_EXECUTION_TIME=900,CACHE_DRIVER=database"
```

**Production (`heroesprofile-website`):** run `gcloud run services describe heroesprofile-website --region=us-east1 --format="value(status.url)"` and use that `.run.app` URL (not `www.heroesprofile.com`) in `CLOUD_TASKS_HANDLER_URL`.

## 6. PHP extension

`google/cloud-tasks` requires the **bcmath** extension (`bccomp()`). The Dockerfiles install it via `docker-php-ext-install bcmath`. Rebuild and redeploy the Cloud Run image after adding it.

## 7. Verify after deploy

1. Open Global Hero on develop with a cache miss.
2. POST `/api/v1/global/hero` → **202** in under 1 second.
3. GCP Console → Cloud Tasks → `global-queries` → task created and delivered.
4. GET `/api/v1/global/status/{job_id}` → **202** then **200**.
5. Reload same filters → POST **200** (cache hit).

## 8. Local development

Set `GLOBAL_ASYNC_ENABLED=false` in `.env`. Global Hero uses synchronous `Cache::remember()` (no Cloud Tasks).

## 9. Develop test checklist

After deploying branch `FixCloudflareTimeoutIssues`:

1. Hard refresh Global Hero on develop.
2. Network tab — POST `/api/v1/global/hero`:
   - Cache miss: **202** in under 1 second, response includes `job_id`.
   - Cache hit (reload same filters): **200** immediately with data.
3. GCP Console → Cloud Tasks → `global-queries` — task created and status **dispatched**.
4. Network tab — GET `/api/v1/global/status/{job_id}`:
   - Returns **202** with `pending` or `processing`, then **200** with hero JSON.
5. Cloud Run logs — `Cloud Task enqueued` on POST, `Global query job complete` on worker.
6. No **524** on POST or status poll requests.
