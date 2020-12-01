<?php
namespace Magecomp\S3Amazon\Model\Command;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Helper\File\StorageFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magecomp\S3Amazon\Helper\Data as DataHelper;
use Magecomp\S3Amazon\Model\Core\File\Storage\S3;
use Aws\S3\S3Client;

class S3Export extends Command
{
    protected $state;
    protected $helper;
    protected $coreFileStorage;
    protected $destinationModel;
    public function __construct(
        State $state,
        Database $storageHelper,
        StorageFactory $coreFileStorageFactory,
        DataHelper $helper,
        S3 $s3Model
    ) {
        $this->state = $state;
        $this->coreFileStorage = $coreFileStorageFactory->create();
        $this->helper = $helper;
        $this->destinationModel = $s3Model;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('amazons3:export');
        $this->setDescription('Sync all  media to Amazon S3.');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->state->emulateAreaCode(Area::AREA_ADMINHTML, function () use ($output) {
            $errors = $this->validate();
            if ($errors) {
                $output->writeln('<error>' . implode('</error>' . PHP_EOL . '<error>', $errors) . '</error>');
                return 1;
            }
            $options = [
                'version' => 'latest',
                'region' => $this->helper->getRegion(),
                'credentials' => [
                    'key' => $this->helper->getAccessKey(),
                    'secret' => $this->helper->getSecretKey(),
                ],
            ];
            if ($this->helper->getEndpointEnabled()) {
                if ($this->helper->getEndpoint()) {
                    $options['endpoint'] = $this->helper->getEndpoint();
                }
                if ($this->helper->getEndpointRegion()) {
                    $options['region'] = $this->helper->getEndpointRegion();
                }
            }
            try {
                $client = new S3Client($options);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                return 1;
            }
            if (!$client->doesBucketExist($this->helper->getBucket())) {
                $output->writeln('<error>The AWS Authentication Failed</error>');
                return 1;
            }
            if ($this->coreFileStorage->getCurrentStorageCode() === \Magecomp\S3Amazon\Model\Core\File\Storage::STORAGE_MEDIA_S3) {
                $output->writeln('<error>You are already using S3 as your media file storage backend!</error>');
                return 1;
            }
            $sourceModel = $this->coreFileStorage->getStorageModel();
            $offset = 0;
            while (($files = $sourceModel->exportFiles($offset, 1)) !== false) {
                foreach ($files as $file) {
                    $object = ltrim($file['directory'] . '/' . $file['filename'], '/');
                    $output->writeln(sprintf('Uploading %s to use S3.', $object));
                }
                $this->destinationModel->importFiles($files);
                $offset += count($files);
            }
            return 0;
        });
    }
    public function validate()
    {
        $errors = [];
        if ($this->helper->getAccessKey() === null) {
            $errors[] = 'You have not provided an AWS access key ID.';
        }
        if ($this->helper->getSecretKey() === null) {
            $errors[] = 'You have not provided an AWS secret access key.';
        }
        if ($this->helper->getBucket() === null) {
            $errors[] = 'You have not provided an S3 bucket.';
        }
        if ($this->helper->getRegion() === null) {
            $errors[] = 'You have not provided an S3 region.';
        }
        return $errors;
    }
}