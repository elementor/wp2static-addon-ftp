(function( $ ) {
    'use strict';

    $(
        function() {
            deploy_options['ftp'] = {
                exportSteps: [
                'ftp_prepare_export',
                'ftp_transfer_files',
                'finalize_deployment'
                ],
                required_fields: {
                    ftpServer: 'Please specify the FTP server address needed to transfer your files via FTP',
                    ftpUsername: 'Please input an FTP username in order to authenticate when using the FTP deployment method.',
                    ftpPassword: 'Please input an FTP password in order to authenticate when using the FTP deployment method.',
                }
            };

            status_descriptions['ftp_prepare_export'] = 'Preparing files for FTP deployment';
            status_descriptions['ftp_transfer_files'] = 'Deploying files via FTP';
        }
    ); // end DOM ready

})( jQuery );
