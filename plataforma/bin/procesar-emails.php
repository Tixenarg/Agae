<?php

declare(strict_types=1);

use App\Repositories\AccesoRepository;
use App\Repositories\EmailRepository;
use App\Services\AccesoPdfService;
use App\Services\EmailService;
use App\Services\MailTemplateService;
use App\Services\ProcesadorEmailService;
use App\Services\QrCodeService;

require_once dirname(__DIR__)
    . '/vendor/autoload.php';

/*
 * ---------------------------------------------------------
 * LOCK DEL WORKER
 * Evita dos ejecuciones simultáneas del cron.
 * ---------------------------------------------------------
 */

$directorioLocks =
    dirname(__DIR__)
    . '/storage/locks';

if (
    !is_dir($directorioLocks)
    && !mkdir(
        $directorioLocks,
        0775,
        true
    )
    && !is_dir($directorioLocks)
) {
    exit(
        "No se pudo crear el directorio de locks.\n"
    );
}

$lockFile =
    fopen(
        $directorioLocks
        . '/email.lock',
        'c'
    );

if ($lockFile === false) {
    exit(
        "No se pudo crear el lock.\n"
    );
}

if (
    !flock(
        $lockFile,
        LOCK_EX | LOCK_NB
    )
) {
    fclose($lockFile);

    exit(
        "Ya hay un proceso de emails ejecutándose.\n"
    );
}

try {
    /*
     * -----------------------------------------------------
     * BASE DE DATOS
     * -----------------------------------------------------
     */

    /** @var PDO $pdo */
    $pdo =
        require dirname(__DIR__)
            . '/config/database.php';

    /*
     * -----------------------------------------------------
     * DEPENDENCIAS
     * -----------------------------------------------------
     */

    $emailRepository =
        new EmailRepository($pdo);

    $accesoRepository =
        new AccesoRepository($pdo);

    $qrCodeService =
        new QrCodeService();

    $pdfService =
        new AccesoPdfService(
            $accesoRepository,
            $qrCodeService
        );

    $templateService =
        new MailTemplateService();

    $emailService =
        new EmailService();

    $procesador =
        new ProcesadorEmailService(
            $emailRepository,
            $accesoRepository,
            $pdfService,
            $templateService,
            $emailService
        );

    /*
     * -----------------------------------------------------
     * BUSCAR EMAILS PENDIENTES
     * -----------------------------------------------------
     */

    $pendientes =
        $emailRepository
            ->obtenerPendientes(10);

    if ($pendientes === []) {
        echo "No hay emails pendientes.\n";

        exit;
    }

    /*
     * -----------------------------------------------------
     * PROCESAR COLA
     * -----------------------------------------------------
     */

    foreach ($pendientes as $email) {
        try {
            $procesador
                ->procesar(
                    $email
                );

            echo sprintf(
                "✔ Email %d enviado.\n",
                (int) $email['id']
            );
        } catch (Throwable $exception) {
            echo sprintf(
                "✖ Email %d: %s\n",
                (int) $email['id'],
                $exception->getMessage()
            );
        }
    }

    echo "\nProceso finalizado.\n";

} catch (Throwable $exception) {
    fwrite(
        STDERR,
        sprintf(
            "ERROR GENERAL: %s\n",
            $exception->getMessage()
        )
    );

    exit(1);

} finally {
    /*
     * -----------------------------------------------------
     * LIBERAR LOCK SIEMPRE
     * -----------------------------------------------------
     */

    flock(
        $lockFile,
        LOCK_UN
    );

    fclose(
        $lockFile
    );
}