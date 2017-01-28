<?php
/**
 * Created by: MinutePHP framework
 */
namespace App\Controller\Audio {

    use Minute\Event\Dispatcher;
    use Minute\Event\UserUploadEvent;
    use Minute\File\TmpDir;
    use Minute\Session\Session;
    use Minute\Utils\PathUtils;

    class SoundRecorder {
        /**
         * @var Dispatcher
         */
        private $dispatcher;
        /**
         * @var PathUtils
         */
        private $pathUtils;
        /**
         * @var TmpDir
         */
        private $tmpDir;
        /**
         * @var Session
         */
        private $session;

        /**
         * SoundRecorder constructor.
         *
         * @param Dispatcher $dispatcher
         * @param PathUtils $pathUtils
         * @param TmpDir $tmpDir
         * @param Session $session
         */
        public function __construct(Dispatcher $dispatcher, PathUtils $pathUtils, TmpDir $tmpDir, Session $session) {
            $this->dispatcher = $dispatcher;
            $this->pathUtils  = $pathUtils;
            $this->tmpDir     = $tmpDir;
            $this->session    = $session;
        }

        public function index() {
            $content = file_get_contents('php://input');
            $wavFile = $this->tmpDir->getTempFile('wav');

            if (file_put_contents($wavFile, $content)) {
                $mp3File = preg_replace('/(\.wav)$/', '.mp3', $wavFile);
                system(sprintf('ffmpeg -y -i "%s" -qscale:a 2 "%s"', $wavFile, $mp3File));

                try {
                    if (file_exists($mp3File)) {
                        $user_id = $this->session->getLoggedInUserId();
                        $event   = new UserUploadEvent($user_id, $mp3File, basename($mp3File), 'file');
                        $this->dispatcher->fire(UserUploadEvent::USER_UPLOAD_FILE, $event);

                        $url = $event->getUrl();
                    }
                } finally {
                    @unlink($wavFile);
                    @unlink($mp3File);
                }
            }

            return json_encode(['url' => $url ?? '']);
        }
    }
}