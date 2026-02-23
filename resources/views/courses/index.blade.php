<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Courses Frontend</title>
    <style>
        :root {
            --bg: #f7f9fc;
            --panel: #ffffff;
            --border: #d8dee9;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #0f766e;
            --danger: #b91c1c;
            --ok: #166534;
            --shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top right, #d1fae5 0%, var(--bg) 35%);
            color: var(--text);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem 2rem;
            display: grid;
            gap: 1rem;
        }

        .hero {
            background: linear-gradient(135deg, #0f766e 0%, #1d4ed8 100%);
            color: #fff;
            border-radius: 14px;
            padding: 1.25rem;
            box-shadow: var(--shadow);
        }

        .hero h1 {
            margin: 0 0 0.35rem;
            font-size: 1.5rem;
        }

        .hero p {
            margin: 0;
            opacity: 0.95;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow);
            padding: 1rem;
        }

        .grid-2 {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        h2 {
            margin: 0 0 0.75rem;
            font-size: 1.05rem;
        }

        .muted {
            color: var(--muted);
            font-size: 0.88rem;
        }

        .controls {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: end;
        }

        .field {
            display: grid;
            gap: 0.35rem;
            min-width: 160px;
            flex: 1;
        }

        label {
            font-size: 0.85rem;
            color: var(--muted);
        }

        input, textarea {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 0.55rem 0.7rem;
            width: 100%;
            font: inherit;
            background: #fff;
        }

        textarea {
            min-height: 84px;
            resize: vertical;
        }

        button {
            border: none;
            border-radius: 10px;
            padding: 0.6rem 0.9rem;
            font: inherit;
            color: #fff;
            background: var(--primary);
            cursor: pointer;
            transition: transform 0.12s ease, opacity 0.12s ease;
            white-space: nowrap;
        }

        button:hover {
            transform: translateY(-1px);
            opacity: 0.95;
        }

        button.secondary { background: #475569; }
        button.danger { background: var(--danger); }
        button.ghost {
            background: #fff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        #flash {
            min-height: 1.5rem;
            font-weight: 600;
        }

        #flash.ok { color: var(--ok); }
        #flash.err { color: var(--danger); }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 680px;
        }

        th, td {
            border-bottom: 1px solid #e5e7eb;
            padding: 0.65rem 0.45rem;
            text-align: left;
            vertical-align: top;
            font-size: 0.92rem;
        }

        th { color: #334155; }

        .pill {
            display: inline-block;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            border-radius: 999px;
            padding: 0.14rem 0.5rem;
            font-size: 0.8rem;
        }

        .row-actions {
            display: flex;
            gap: 0.35rem;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
<main class="container">
    <section class="hero">
        <h1>Course Manager</h1>
    </section>

    <section class="panel">
        <div class="controls">
            <div class="field">
                <label for="search">Search Courses</label>
                <input id="search" type="text" placeholder="Search name or description">
            </div>
            <button id="searchBtn" type="button">Search</button>
            <button id="resetBtn" class="ghost" type="button">Reset</button>
            <button id="refreshBtn" class="secondary" type="button">Refresh</button>
        </div>
    </section>

    <section id="flash" class="panel muted">Ready.</section>

    <section class="grid-2">
        <article class="panel">
            <h2>Create Course</h2>
            <div class="field">
                <label for="createName">Name</label>
                <input id="createName" type="text">
            </div>
            <div class="field">
                <label for="createDescription">Description</label>
                <textarea id="createDescription"></textarea>
            </div>
            <div class="field">
                <label for="createPrice">Price (cents)</label>
                <input id="createPrice" type="number" min="0">
            </div>
            <button id="createBtn" type="button">Create</button>
        </article>

        <article class="panel">
            <h2>Update Course</h2>
            <p class="muted">Click “Edit” in the table to prefill fields.</p>
            <div class="field">
                <label for="updateId">Course ID</label>
                <input id="updateId" type="number" min="1">
            </div>
            <div class="field">
                <label for="updateName">Name</label>
                <input id="updateName" type="text">
            </div>
            <div class="field">
                <label for="updateDescription">Description</label>
                <textarea id="updateDescription"></textarea>
            </div>
            <div class="field">
                <label for="updatePrice">Price (cents)</label>
                <input id="updatePrice" type="number" min="0">
            </div>
            <div class="controls">
                <button id="updateBtn" type="button">Update</button>
                <button id="clearUpdateBtn" class="ghost" type="button">Clear</button>
            </div>
        </article>
    </section>

    <section class="grid-2">
        <article class="panel">
            <h2>Bulk Delete</h2>
            <div class="field">
                <label for="bulkDeleteIds">IDs (comma-separated)</label>
                <input id="bulkDeleteIds" type="text" placeholder="1,2,3">
            </div>
            <button id="bulkDeleteBtn" class="danger" type="button">Bulk Delete</button>
        </article>

        <article class="panel">
            <h2>Bulk Update</h2>
            <div class="field">
                <label for="bulkUpdateIds">IDs (comma-separated)</label>
                <input id="bulkUpdateIds" type="text" placeholder="1,2,3">
            </div>
            <div class="field">
                <label for="bulkUpdateName">Name (optional)</label>
                <input id="bulkUpdateName" type="text">
            </div>
            <div class="field">
                <label for="bulkUpdateDescription">Description (optional)</label>
                <textarea id="bulkUpdateDescription"></textarea>
            </div>
            <div class="field">
                <label for="bulkUpdatePrice">Price (cents, optional)</label>
                <input id="bulkUpdatePrice" type="number" min="0">
            </div>
            <button id="bulkUpdateBtn" type="button">Bulk Update</button>
        </article>
    </section>

    <section class="panel">
        <h2>Courses</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="courseRows">
                <tr>
                    <td colspan="5" class="muted">Loading...</td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
(() => {
    const apiBase = '/api/v1/courses';
    const flash = document.getElementById('flash');
    const rowsEl = document.getElementById('courseRows');
    const searchEl = document.getElementById('search');

    let currentSearch = '';
    let courses = [];

    function setFlash(message, isError = false) {
        flash.textContent = message;
        flash.classList.toggle('ok', !isError);
        flash.classList.toggle('err', isError);
        flash.classList.toggle('muted', false);
    }

    function toPriceDisplay(priceCents) {
        const dollars = Number(priceCents) / 100;
        return `$${dollars.toFixed(2)} (${priceCents} cents)`;
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    async function requestJson(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            ...options,
        });

        const hasBody = response.status !== 204;
        const body = hasBody ? await response.json().catch(() => ({})) : null;

        if (!response.ok) {
            const message = body?.message ?? `Request failed with status ${response.status}`;
            const error = new Error(message);
            error.payload = body;
            error.status = response.status;
            throw error;
        }

        return body;
    }

    function renderRows() {
        if (courses.length === 0) {
            rowsEl.innerHTML = '<tr><td colspan="5" class="muted">No courses found.</td></tr>';
            return;
        }

        rowsEl.innerHTML = courses.map((course) => `
            <tr>
                <td><span class="pill">${course.id}</span></td>
                <td>${escapeHtml(course.name)}</td>
                <td>${escapeHtml(course.description)}</td>
                <td>${escapeHtml(toPriceDisplay(course.price_cents))}</td>
                <td>
                    <div class="row-actions">
                        <button type="button" data-action="edit" data-id="${course.id}">Edit</button>
                        <button type="button" class="danger" data-action="delete" data-id="${course.id}">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async function loadCourses() {
        const query = currentSearch.trim() !== '' ? `?search=${encodeURIComponent(currentSearch.trim())}` : '';
        const data = await requestJson(`${apiBase}${query}`);
        courses = Array.isArray(data) ? data : [];
        renderRows();
        setFlash(`Loaded ${courses.length} course(s).`);
    }

    function getRequiredCreatePayload() {
        return {
            name: document.getElementById('createName').value.trim(),
            description: document.getElementById('createDescription').value.trim(),
            price_cents: Number(document.getElementById('createPrice').value),
        };
    }

    function getRequiredUpdatePayload() {
        return {
            name: document.getElementById('updateName').value.trim(),
            description: document.getElementById('updateDescription').value.trim(),
            price_cents: Number(document.getElementById('updatePrice').value),
        };
    }

    function clearUpdateForm() {
        document.getElementById('updateId').value = '';
        document.getElementById('updateName').value = '';
        document.getElementById('updateDescription').value = '';
        document.getElementById('updatePrice').value = '';
    }

    function parseValidationMessage(error) {
        if (!error?.payload?.errors) {
            return error.message;
        }

        const parts = [];
        for (const [field, messages] of Object.entries(error.payload.errors)) {
            parts.push(`${field}: ${messages.join(', ')}`);
        }

        return parts.join(' | ');
    }

    async function onCreate() {
        const payload = getRequiredCreatePayload();
        try {
            await requestJson(apiBase, {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            document.getElementById('createName').value = '';
            document.getElementById('createDescription').value = '';
            document.getElementById('createPrice').value = '';
            await loadCourses();
            setFlash('Course created.');
        } catch (error) {
            setFlash(parseValidationMessage(error), true);
        }
    }

    async function onUpdate() {
        const id = Number(document.getElementById('updateId').value);
        if (!id) {
            setFlash('Update requires a valid course ID.', true);
            return;
        }

        const payload = getRequiredUpdatePayload();
        try {
            await requestJson(`${apiBase}/${id}`, {
                method: 'PUT',
                body: JSON.stringify(payload),
            });
            await loadCourses();
            setFlash(`Course ${id} updated.`);
        } catch (error) {
            setFlash(parseValidationMessage(error), true);
        }
    }

    async function onDelete(id) {
        if (!confirm(`Delete course ${id}?`)) {
            return;
        }

        try {
            await requestJson(`${apiBase}/${id}`, { method: 'DELETE' });
            await loadCourses();
            setFlash(`Course ${id} deleted.`);
        } catch (error) {
            setFlash(error.message, true);
        }
    }

    async function onBulkDelete() {
        const ids = document.getElementById('bulkDeleteIds').value.trim();
        if (!ids) {
            setFlash('Bulk delete requires at least one ID.', true);
            return;
        }

        try {
            const result = await requestJson(`${apiBase}/bulk-delete?ids=${encodeURIComponent(ids)}`, {
                method: 'DELETE',
            });
            await loadCourses();

            if (!result) {
                setFlash('Bulk delete completed: all IDs found.');
                return;
            }

            setFlash(`Bulk delete partial: missing [${(result.not_found_ids ?? []).join(', ')}].`);
        } catch (error) {
            setFlash(parseValidationMessage(error), true);
        }
    }

    async function onBulkUpdate() {
        const ids = document.getElementById('bulkUpdateIds').value.trim();
        if (!ids) {
            setFlash('Bulk update requires at least one ID.', true);
            return;
        }

        const body = {};
        const name = document.getElementById('bulkUpdateName').value.trim();
        const description = document.getElementById('bulkUpdateDescription').value.trim();
        const priceRaw = document.getElementById('bulkUpdatePrice').value.trim();

        if (name !== '') body.name = name;
        if (description !== '') body.description = description;
        if (priceRaw !== '') body.price_cents = Number(priceRaw);

        try {
            const result = await requestJson(`${apiBase}/bulk-update?ids=${encodeURIComponent(ids)}`, {
                method: 'PUT',
                body: JSON.stringify(body),
            });
            await loadCourses();
            const missing = result.not_found_ids ? ` Missing [${result.not_found_ids.join(', ')}].` : '';
            setFlash(`Bulk update complete.${missing}`);
        } catch (error) {
            setFlash(parseValidationMessage(error), true);
        }
    }

    rowsEl.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const action = target.dataset.action;
        const id = Number(target.dataset.id);

        if (!action || !id) {
            return;
        }

        if (action === 'delete') {
            onDelete(id);
            return;
        }

        if (action === 'edit') {
            const course = courses.find((item) => Number(item.id) === id);
            if (!course) {
                return;
            }

            document.getElementById('updateId').value = String(course.id);
            document.getElementById('updateName').value = course.name ?? '';
            document.getElementById('updateDescription').value = course.description ?? '';
            document.getElementById('updatePrice').value = String(course.price_cents ?? '');
            setFlash(`Loaded course ${course.id} into update form.`);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    document.getElementById('searchBtn').addEventListener('click', async () => {
        currentSearch = searchEl.value;
        try {
            await loadCourses();
        } catch (error) {
            setFlash(error.message, true);
        }
    });

    document.getElementById('resetBtn').addEventListener('click', async () => {
        searchEl.value = '';
        currentSearch = '';
        try {
            await loadCourses();
        } catch (error) {
            setFlash(error.message, true);
        }
    });

    document.getElementById('refreshBtn').addEventListener('click', async () => {
        try {
            await loadCourses();
        } catch (error) {
            setFlash(error.message, true);
        }
    });

    document.getElementById('createBtn').addEventListener('click', onCreate);
    document.getElementById('updateBtn').addEventListener('click', onUpdate);
    document.getElementById('clearUpdateBtn').addEventListener('click', clearUpdateForm);
    document.getElementById('bulkDeleteBtn').addEventListener('click', onBulkDelete);
    document.getElementById('bulkUpdateBtn').addEventListener('click', onBulkUpdate);

    loadCourses().catch((error) => {
        setFlash(error.message, true);
    });
})();
</script>
</body>
</html>
