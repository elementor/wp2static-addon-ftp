<?php

namespace WP2Static;

class FTP extends SitePublisher {

    public function __construct() {
        $plugin = Controller::getInstance();

        $this->base_url = 'https://api.netlify.com';

        $this->batch_size = $plugin->options->getOption( 'deployBatchSize' );

        $this->port = isset( $plugin->options->getOption( 'ftpPort' ) ) ?
            $plugin->options->getOption( 'ftpPort' ) : 21;

        $this->use_ftps = isset( $plugin->options->getOption( 'ftpTLS' ) );
        $thia->ftp_server = $plugin->options->getOption( 'ftpServer' );

        $this->ftp_username = $plugin->options->getOption( 'ftpUsername' );
        $this->ftp_password = $plugin->options->getOption( 'ftpPassword' );
        $this->active_ftp = $plugin->options->getOption( 'activeFTP' );
        $this->previous_hashes_path =
            $plugin->options->getOption( 'wp_uploads_path' ) .
                '/WP2STATIC-FTP-PREVIOUS-HASHES.txt';
    }

    public function upload_files() {
        $this->files_remaining = $this->getRemainingItemsCount();

        if ( $this->files_remaining < 0 ) {
            echo 'ERROR';
            die(); }

        if ( $this->batch_size > $this->files_remaining ) {
            $this->batch_size = $this->files_remaining;
        }

        $lines = $this->getItemsToDeploy( $this->batch_size );

        $this->openPreviousHashesFile();

        $this->ftp->connect(
            $this->ftp_server,
            $this->use_ftps,
            $this->port
        );

        $this->ftp->login(
            $this->ftp_username,
            $this->ftp_password,
        );

        if ( isset( $this->activeFTP ) ) {
            $this->ftp->pasv( false );
        } else {
            $this->ftp->pasv( true );
        }

        foreach ( $lines as $line ) {
            list($this->local_file, $this->target_path) = explode( ',', $line );

            $this->local_file = $this->archive->path . $this->local_file;

            if ( ! is_file( $this->local_file ) ) {
                continue; }

            if ( isset( $plugin->options->getOption( 'ftpRemotePath' ) ) ) {
                $this->target_path =
                    $plugin->options->getOption( 'ftpRemotePath' ) . '/' . $this->target_path;
            }

            $this->local_file_contents = file_get_contents( $this->local_file );

            $this->hash_key =
                $this->target_path . basename( $this->local_file );

            if ( isset( $this->file_paths_and_hashes[ $this->hash_key ] ) ) {
                $prev = $this->file_paths_and_hashes[ $this->hash_key ];
                $current = crc32( $this->local_file_contents );

                if ( $prev != $current ) {
                    $this->putFileViaFTP();
                }
            } else {
                $this->putFileViaFTP();
            }

            $this->recordFilePathAndHashInMemory(
                $this->hash_key,
                $this->local_file_contents
            );
        }

        unset( $this->ftp );

        $this->writeFilePathAndHashesToFile();

        $this->pauseBetweenAPICalls();

        if ( $this->uploadsCompleted() ) {
            $this->finalizeDeployment();
        }
    }

    public function test_ftp() {
        require_once $this->ftp_lib_path .
            '/FtpClient.php';
        require_once $this->ftp_lib_path .
            '/FtpException.php';
        require_once $this->ftp_lib_path .
            '/FtpWrapper.php';

        $this->ftp = new \FtpClient\FtpClient();

        $this->port = isset( $plugin->options->getOption( 'ftpPort' ) ) ?
            $plugin->options->getOption( 'ftpPort' ) : 21;

        $this->use_ftps = isset( $plugin->options->getOption( 'ftpTLS' ) );

        $this->ftp->connect(
            $plugin->options->getOption( 'ftpServer' ),
            $this->use_ftps,
            $this->port
        );

        try {
            $this->ftp->login(
                $plugin->options->getOption( 'ftpUsername' ),
                $plugin->options->getOption( 'ftpPassword' )
            );

            if ( ! defined( 'WP_CLI' ) ) {
                echo 'SUCCESS'; }

            unset( $this->ftp );
            return;
        } catch ( Exception $e ) {
            unset( $this->ftp );
            $this->handleException( $e );
        }
    }

    public function putFileViaFTP() {
        if ( ! $this->ftp->isdir( $this->target_path ) ) {
            $mkdir_result = $this->ftp->mkdir( $this->target_path, true );
        }

        $this->ftp->chdir( $this->target_path );
        $this->ftp->putFromPath( $this->local_file );
        $this->ftp->chdir( '/' );
    }
}

$ftp = new WP2Static_FTP();