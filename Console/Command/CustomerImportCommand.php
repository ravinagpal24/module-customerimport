<?php
namespace VML\CustomerImport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File;

class CustomerImportCommand extends Command
{
    const PROFILE_JSON = 'sample-json';
    const PROFILE_CSV = 'sample-csv';

    /**
     * @var State
     */
    private $state;

    /**
     * @var Csv
     */
    private $csvProcessor;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var File
     */
    private $file;

    public function __construct(
        State $state,
        Csv $csvProcessor,
        DirectoryList $directoryList,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        File $file
    ) {
        $this->state = $state;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->file = $file;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('customer:import')
            ->setDescription('Import customers from a CSV or JSON file')
            ->addArgument('profile-name', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Profile name')
            ->addArgument('source', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Source file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $profileName = $input->getArgument('profile-name');
        $sourceFile = $input->getArgument('source');

        switch ($profileName) {
            case self::PROFILE_JSON:
                $this->importFromJson($sourceFile, $output);
                break;
            case self::PROFILE_CSV:
                $this->importFromCsv($sourceFile, $output);
                break;
            default:
                $output->writeln("<error>Unknown profile name '{$profileName}'. Please provide a valid profile.</error>");
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    private function importFromJson($sourceFile, $output)
    {
        $jsonFilePath = $this->directoryList->getPath(DirectoryList::ROOT) . '/var/import/' . $sourceFile;
        if (!$this->file->fileExists($jsonFilePath)) {
            $output->writeln("<error>JSON file '{$sourceFile}' not found.</error>");
            return;
        }

        $jsonData = json_decode(file_get_contents($jsonFilePath), true);

        if (!is_array($jsonData)) {
            $output->writeln("<error>Invalid JSON data in '{$sourceFile}'.</error>");
            return;
        }

        foreach ($jsonData as $customerData) {
            try {
                $customer = $this->customerFactory->create();
                $customer->setWebsiteId(1); // Default website ID
                $customer->setEmail($customerData['emailaddress']);
                $customer->setFirstname($customerData['fname']);
                $customer->setLastname($customerData['lname']);
                $customer->setGroupId(1); // General Customer group ID

                $this->customerRepository->save($customer);
                $output->writeln("<info>Customer '{$customerData['emailaddress']}' imported successfully.</info>");
            } catch (LocalizedException $e) {
                $output->writeln("<error>Unable to save customer '{$customerData['emailaddress']}'. Error: {$e->getMessage()}</error>");
            } catch (NoSuchEntityException $e) {
                $output->writeln("<error>No such entity error: {$e->getMessage()}</error>");
            }
        }
    }

    private function importFromCsv($sourceFile, $output)
    {
        $csvFilePath = $this->directoryList->getPath(DirectoryList::ROOT) . '/var/import/' . $sourceFile;
        if (!$this->file->fileExists($csvFilePath)) {
            $output->writeln("<error>CSV file '{$sourceFile}' not found.</error>");
            return;
        }

        $rows = $this->csvProcessor->getData($csvFilePath);

        if (count($rows) < 2) {
            $output->writeln("<error>CSV file '{$sourceFile}' is empty or invalid.</error>");
            return;
        }

        // Assuming first row is header
        $headers = array_shift($rows);

        foreach ($rows as $data) {
            $customerData = array_combine($headers, $data);
            try {
                $customer = $this->customerFactory->create();
                $customer->setWebsiteId(1); // Default website ID
                $customer->setEmail($customerData['emailaddress']);
                $customer->setFirstname($customerData['fname']);
                $customer->setLastname($customerData['lname']);
                $customer->setGroupId(1); // General Customer group ID

                $this->customerRepository->save($customer);
                $output->writeln("<info>Customer '{$customerData['emailaddress']}' imported successfully.</info>");
            } catch (LocalizedException $e) {
                $output->writeln("<error>Unable to save customer '{$customerData['emailaddress']}'. Error: {$e->getMessage()}</error>");
            } catch (NoSuchEntityException $e) {
                $output->writeln("<error>No such entity error: {$e->getMessage()}</error>");
            }
        }
    }
}
