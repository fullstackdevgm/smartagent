<?php
/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */
namespace Renegade\Infusionsoft\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;
use Renegade\Infusionsoft\Service\InfusionsoftService;

class CustomerLoginObserver implements ObserverInterface
{
    const XML_PATH_RENEGADE_INFUSIONSOFT_GENERAL_ENABLED = 'renegade_infusionsoft/general/enabled';

    /**
     * InfusionsoftService object
     *
     * @var InfusionsoftService
     */
    protected $infusionsoftService;

    /**
     * Contact fields to select from Infusionsoft API
     *
     * @var []
     */
    protected $contactFieldsToSelect = [
        'Id',
        'Email',
        'Password',
        'FirstName',
        'LastName',
        'Groups',
        'State2',
        'PostalCode2',
        'Address2Street1',
        'Address2Street2',
        'City2',
        'Country2',
        'Phone1',
        'State',
        'PostalCode',
        'StreetAddress1',
        'StreetAddress2',
        'City',
        'Country',
    ];

    /**
     * Infusionsoft contact data
     *
     * @var array
     */
    protected $infusionsoftContacts = [];

    /**
     * CustomerRepository object
     *
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * AddressInterface factory
     *
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * Region model factory
     *
     * @var RegionFactory
     */
    private $regionModelFactory;

    /**
     * Region factory
     *
     * @var RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * Encryptor interface
     *
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Customer factory
     *
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * CountryInformationAcquirer object
     *
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * Logger object
     *
     * @var LoggerInterface
     */
    private $logger;

    /** @var Data */
    private $jsonHelper;

    /**
     * CustomerLoginObserver constructor.
     *
     * @param InfusionsoftService                 $infusionsoftService
     * @param AddressInterfaceFactory             $addressFactory
     * @param RegionFactory                       $regionModelFactory
     * @param RegionInterfaceFactory              $regionFactory
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param EncryptorInterface                  $encryptor
     * @param CustomerRepositoryInterface         $customerRepository
     * @param CustomerInterfaceFactory            $customerFactory
     * @param LoggerInterface                     $logger
     * @param Data                                $jsonHelper
     *
     * @internal param Country $country
     */
    public function __construct(
        InfusionsoftService $infusionsoftService,
        AddressInterfaceFactory $addressFactory,
        RegionFactory $regionModelFactory,
        RegionInterfaceFactory $regionFactory,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        EncryptorInterface $encryptor,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerFactory,
        LoggerInterface $logger,
        Data $jsonHelper
    ) {
        $this->infusionsoftService = $infusionsoftService;
        $this->addressFactory = $addressFactory;
        $this->regionModelFactory = $regionModelFactory;
        $this->regionFactory = $regionFactory;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->encryptor = $encryptor;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Execute the observer
     *
     * @param Observer $observer
     *
     * @throws \Exception
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (
            $this->infusionsoftService->isEnabled()
            && $this->infusionsoftService->hasValidToken()
        ) {
            $credentials = $this->getCredentials($observer->getData('request'));

            if ($credentials['username'] && $credentials['password']) {
                try {
                    $this->customerRepository->get($credentials['username']);
                } catch (NoSuchEntityException $e) {
                    try {
                        $this->createCustomerFromInfusionsoftContact(
                            $credentials['username'],
                            $credentials['password']
                        );
                    } catch (\Exception $e) {
                        $this->logger->critical($e);

                        throw $e;
                    }
                }
            }
        }
    }

    /**
     * Get username and password from HTTP request object
     *
     * @param Http $request
     *
     * @return array
     */
    protected function getCredentials(Http $request)
    {
        $credentials = [
            'username' => null,
            'password' => null,
        ];

        if ($request->has('login')) {
            $loginData = $request->getParam('login');

            if (isset($loginData['username']) && isset($loginData['password'])) {
                $credentials['username'] = strtolower(trim($loginData['username']));
                $credentials['password'] = $loginData['password'];
            }
        } elseif ($request->isAjax()) {
            $ajaxRequestData = $this->jsonHelper->jsonDecode($request->getContent());

            if (!empty($ajaxRequestData['username']) && !empty($ajaxRequestData['password'])) {
                $credentials['username'] = strtolower(trim($ajaxRequestData['username']));
                $credentials['password'] = $ajaxRequestData['password'];
            }
        }

        return $credentials;
    }

    /**
     * Try to create a new Magento customer from Infusionsoft contact
     *
     * @param string $username
     * @param string $password
     *
     * @throws NoSuchEntityException
     *
     * @return void
     */
    protected function createCustomerFromInfusionsoftContact($username, $password)
    {
        $infusionsoftContact = $this->getInfusionsoftContact($username);

        if ($infusionsoftContact && $password === $infusionsoftContact['Password']) {
            if (!in_array(7919, $infusionsoftContact['Groups'])) {
                throw new NoSuchEntityException(__('You are not currently a member of the Book Leads program.'));
            }

            /** @var CustomerInterface $customer */
            $customer = $this->customerFactory->create();

            $customer
                ->setEmail($infusionsoftContact['Email'])
                ->setFirstname($infusionsoftContact['FirstName'])
                ->setLastname($infusionsoftContact['LastName']);

            $customer->setAddresses(
                [
                    $this->getBillingAddress($infusionsoftContact),
                    $this->getShippingAddress($infusionsoftContact),
                ]
            );

            $passwordHash = $this->encryptor->getHash($infusionsoftContact['Password'], true);

            $this->customerRepository->save($customer, $passwordHash);
        }
    }

    /**
     * Get Infusionsoft contact details
     *
     * @param string $emailAddress
     *
     * @return array
     */
    protected function getInfusionsoftContact($emailAddress)
    {
        $contactKey = sha1($emailAddress);

        if (!isset($this->infusionsoftContacts[$contactKey])) {
            $infusionsoftContactLookup = $this->infusionsoftService
                ->getInfusionsoft()
                ->contacts()
                ->findByEmail($emailAddress, $this->contactFieldsToSelect);

            if (count($infusionsoftContactLookup)) {
                $infusionsoftContact = $infusionsoftContactLookup[0];

                foreach ($this->contactFieldsToSelect as $field) {
                    if (!isset($infusionsoftContact[$field])) {
                        $infusionsoftContact[$field] = null;
                    }
                }

                if (isset($infusionsoftContact['Groups'])) {
                    $contactGroups = [];

                    foreach (explode(',', $infusionsoftContact['Groups']) as $group) {
                        $contactGroups[] = intval($group);
                    }

                    $infusionsoftContact['Groups'] = $contactGroups;
                }

                $this->infusionsoftContacts[$contactKey] = $infusionsoftContact;
            }
        }

        return $this->infusionsoftContacts[$contactKey];
    }

    /**
     * Get billing address
     *
     * @param array $infusionsoftContact
     *
     * @return AddressInterface
     */
    protected function getBillingAddress($infusionsoftContact)
    {
        /** @var AddressInterface $billingAddress */
        $billingAddress = $this->addressFactory->create();

        $billingAddressCountry = $this->countryInformationAcquirer
            ->getCountryInfo($this->getCountryCode($infusionsoftContact['Country']));

        $billingAddress
            ->setFirstname($infusionsoftContact['FirstName'])
            ->setLastname($infusionsoftContact['LastName'])
            ->setStreet(
                [
                    $infusionsoftContact['StreetAddress1'],
                    $infusionsoftContact['StreetAddress2'],
                ]
            )
            ->setCity($infusionsoftContact['City']);

        $billingAddress
            ->setRegionId(
                $this->lookupRegionId(
                    $infusionsoftContact['State'],
                    $billingAddressCountry->getId()
                )
            )
            ->setCountryId($billingAddressCountry->getId())
            ->setPostcode($infusionsoftContact['PostalCode'])
            ->setTelephone($infusionsoftContact['Phone1'])
            ->setIsDefaultBilling(true);

        return $billingAddress;
    }

    /**
     * Get shipping address
     *
     * @param array $infusionsoftContact
     *
     * @return AddressInterface
     */
    protected function getShippingAddress($infusionsoftContact)
    {
        /**
         * @var AddressInterface $shippingAddress
         */
        $shippingAddress = $this->addressFactory->create();

        $shippingAddressCountry = $this->countryInformationAcquirer
            ->getCountryInfo($this->getCountryCode($infusionsoftContact['Country2']));

        $shippingAddress
            ->setFirstname($infusionsoftContact['FirstName'])
            ->setLastname($infusionsoftContact['LastName'])
            ->setStreet(
                [
                    $infusionsoftContact['Address2Street1'],
                    $infusionsoftContact['Address2Street2'],
                ]
            )
            ->setCity($infusionsoftContact['City2']);

        $shippingAddress
            ->setRegionId(
                $this->lookupRegionId(
                    $infusionsoftContact['State2'],
                    $shippingAddressCountry->getId()
                )
            )
            ->setCountryId($shippingAddressCountry->getId())
            ->setPostcode($infusionsoftContact['PostalCode2'])
            ->setTelephone($infusionsoftContact['Phone1'])
            ->setIsDefaultShipping(true);

        return $shippingAddress;
    }

    /**
     * Get country code from name
     *
     * @param string $country
     *
     * @return int|string
     */
    protected function getCountryCode($country)
    {
        $countryCodes = [
            'Canada'        => 'CA',
            'United States' => 'US',
        ];

        return isset($countryCodes[$country])
            ? $countryCodes[$country]
            : $country;
    }

    /**
     * Get Region ID
     *
     * @param string $region
     * @param int    $countryId
     *
     * @return int|null
     */
    protected function lookupRegionId($region, $countryId)
    {
        return $this->regionModelFactory
            ->create()
            ->loadByCode($region, $countryId)
            ->getId();
    }
}
