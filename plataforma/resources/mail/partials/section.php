<?php

declare(strict_types=1);

?>

<tr>
    <td
        style="
            padding: 24px 32px;
            border-top: 1px solid #e6e6e6;
        "
    >
        <div
            style="
                margin-bottom: 12px;
                font-size: 12px;
                line-height: 1.4;
                font-weight: 700;
                letter-spacing: 1px;
                text-transform: uppercase;
                color: #777777;
            "
        >
            <?= htmlspecialchars(
                $tituloSeccion
            ) ?>
        </div>

        <?= $contenidoSeccion ?>
    </td>
</tr>