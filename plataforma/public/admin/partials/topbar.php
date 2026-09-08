<?php

declare(strict_types=1);

?>

<header class="admin-topbar">

    <div class="topbar-left">

        <button
            type="button"
            class="sidebar-toggle"
            id="sidebar-toggle"
            aria-label="Abrir menú"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div>

            <h1 class="topbar-title">
                <?= htmlspecialchars(
                    $titulo
                ) ?>
            </h1>

            <div class="topbar-breadcrumb">
                AGAE Platform
            </div>

        </div>

    </div>

    <div class="topbar-event">

        <div class="topbar-event-info">

            <div class="topbar-event-name">
                Evento 6N26
            </div>

            <div class="topbar-event-date">
                6 de noviembre · 20:30 h
            </div>

        </div>

        <div class="topbar-status">
            <span class="topbar-status-dot"></span>
            Activo
        </div>

    </div>

</header>