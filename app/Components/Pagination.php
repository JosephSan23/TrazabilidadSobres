<?php

declare(strict_types=1);

namespace App\Components;

use App\Core\Url;

final class Pagination
{
    public static function render(
        int $currentPage,
        int $totalPages,
        string $path,
        array $query = []
    ): void {
        if ($totalPages <= 1) {
            return;
        }

        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        ?>
        <nav aria-label="Paginación">
            <ul class="pagination justify-content-end mb-0">
                <li class="page-item <?= $currentPage === 1 ? 'disabled' : '' ?>">
                    <a
                        class="page-link"
                        href="<?= htmlspecialchars(
                            Url::to($path, array_merge($query, [
                                'page' => max(1, $currentPage - 1),
                            ])),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                        Anterior
                    </a>
                </li>

                <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                    <li class="page-item <?= $page === $currentPage ? 'active' : '' ?>">
                        <a
                            class="page-link"
                            href="<?= htmlspecialchars(
                                Url::to($path, array_merge($query, [
                                    'page' => $page,
                                ])),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >
                            <?= $page ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $currentPage === $totalPages ? 'disabled' : '' ?>">
                    <a
                        class="page-link"
                        href="<?= htmlspecialchars(
                            Url::to($path, array_merge($query, [
                                'page' => min($totalPages, $currentPage + 1),
                            ])),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                        Siguiente
                    </a>
                </li>
            </ul>
        </nav>
        <?php
    }

    private function __construct()
    {
    }
}