<?php

namespace PushSoft\ChangeCustomerPassword\Console\Command;

use Amazon\Login\Plugin\CustomerRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetCustomerPassword extends \Symfony\Component\Console\Command\Command
{
    /* @var CustomerRepository */
    protected $customerRepository;
    /**/
    protected $customerRegistry;


    /**
     * SetCustomerPassword constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerRegistry $customerRegistry
     * @param CustomerFactory $customerFactory
     * @param string|null $name
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerRegistry $customerRegistry,
        string $name = null
    )
    {
        $this->customerRegistry = $customerRegistry;
        $this->customerRepository = $customerRepository;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('customer:password:set');
        $this->setDescription('Programatically set customer password');

        $this->addArgument("customerIdOrEmail", InputArgument::REQUIRED, "Customer Id or customer email");
        $this->addArgument("password", InputArgument::REQUIRED, "New password");
        $this->addArgument("websiteId", InputArgument::OPTIONAL, "Website Id", 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $customerData = $input->getArgument("customerIdOrEmail");
        $isEmail = (bool)filter_var($customerData, FILTER_VALIDATE_EMAIL);
        $customer = $isEmail ? $this->customerRepository->get($customerData, $input->getArgument("websiteId")) :
            $customerModel = $this->customerRepository->getById($customerData);
        $customerSecure = $this->customerRegistry->retrieve($customer->getId());
        $customerSecure->setPassword($input->getArgument("password"));
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $this->customerRepository->save($customer);
        $output->writeln("[+] The new password has been set.");
    }
}
