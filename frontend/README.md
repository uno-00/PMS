# Frontend (UI assets)

Blade views, Tailwind CSS, Alpine.js, Chart.js, and Vite build config for the PMS Livewire UI.

## Run locally

```bash
cd frontend
npm install
npm run dev
```

Production build (writes to `backend/public/build/`):

```bash
npm run build
```

From repo root:

```bash
npm run build
```

## Key paths

| Path | Purpose |
|------|---------|
| `resources/views/` | Blade templates and Livewire views |
| `resources/css/app.css` | Tailwind design system |
| `resources/js/` | Chart.js, Quill editor, app bootstrap |
| `vite.config.js` | Vite + Laravel plugin config |

Backend API and business logic live in [`../backend/`](../backend/).
