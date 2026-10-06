<div class="table-responsive" id="github-commits">
    <table class="table table-hover align-middle mb-0">
        <thead style="background-color: #f1f5f9;">
            <tr>
                <th class="px-4 py-3 text-secondary" style="width: 120px;">Commit</th>
                <th class="px-4 py-3 text-secondary">Name</th>
                <th class="px-4 py-3 text-secondary" style="width: 180px;">Author</th>
                <th class="px-4 py-3 text-secondary" style="width: 140px;">Date</th>
            </tr>
        </thead>

        <tbody>
            @if (!empty($commitError))
                <tr>
                    <td colspan="4" class="text-center text-danger py-4">
                        <div class="fw-semibold mb-1">Unable to load commits</div>
                        <small>{{ $commitError }}</small>
                    </td>
                </tr>
            @endif

            @forelse ($commits ?? [] as $commit)
                <tr data-commit-row>
                    <td class="px-4 py-3">
                        <a href="{{ $commit['url'] ?? '#' }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-decoration-none">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle font-monospace px-2 py-1">
                                {{ $commit['short_sha'] ?? 'N/A' }}
                            </span>
                        </a>
                    </td>

                    <td class="px-4 py-3">
                        <a href="{{ $commit['url'] ?? '#' }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-decoration-none text-dark fw-medium">
                            {{ $commit['message'] ?? 'No message' }}
                        </a>
                    </td>

                    <td class="px-4 py-3">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">
                            {{ $commit['author'] ?? 'Unknown' }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-secondary">
                        @php
                            $commitDate = $commit['date'] ?? null;
                            $formattedDate = 'N/A';

                            if (!empty($commitDate)) {
                                try {
                                    $parsedDate = \Carbon\Carbon::parse($commitDate);

                                    if ($parsedDate->isValid()) {
                                        $formattedDate = $parsedDate->format('M d, Y');
                                    }
                                } catch (\Throwable $e) {
                                    $formattedDate = 'N/A';
                                }
                            }
                        @endphp

                        {{ $formattedDate }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">
                        <div class="mb-2 text-primary" style="font-size: 1.5rem;">↳</div>
                        <div class="fw-semibold">No commits found</div>
                        <small>There are no commits to display for this repository.</small>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<nav class="d-flex align-items-center justify-content-between mt-3"
     id="github-commits-pagination"
     aria-label="Commit pagination"
     hidden>
    <button type="button" class="btn btn-dark btn-sm" data-commits-previous disabled>
        Previous
    </button>
    <span class="small text-secondary" data-commits-page aria-live="polite"></span>
    <button type="button" class="btn btn-dark btn-sm" data-commits-next>
        Next
    </button>
</nav>

<script>
    (() => {
        const table = document.querySelector('#github-commits');
        const pagination = document.querySelector('#github-commits-pagination');

        if (!table || !pagination) {
            return;
        }

        const rows = Array.from(table.querySelectorAll('[data-commit-row]'));
        const previous = pagination.querySelector('[data-commits-previous]');
        const next = pagination.querySelector('[data-commits-next]');
        const pageLabel = pagination.querySelector('[data-commits-page]');
        const pageSize = 10;
        const pageCount = Math.ceil(rows.length / pageSize);
        let currentPage = 1;

        if (pageCount <= 1) {
            return;
        }

        pagination.hidden = false;

        const renderPage = () => {
            const firstVisibleRow = (currentPage - 1) * pageSize;

            rows.forEach((row, index) => {
                row.hidden = index < firstVisibleRow || index >= firstVisibleRow + pageSize;
            });

            previous.disabled = currentPage === 1;
            next.disabled = currentPage === pageCount;
            pageLabel.textContent = `Page ${currentPage} of ${pageCount} (${rows.length} commits)`;
        };

        previous.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage -= 1;
                renderPage();
            }
        });

        next.addEventListener('click', () => {
            if (currentPage < pageCount) {
                currentPage += 1;
                renderPage();
            }
        });

        renderPage();
    })();
</script>
