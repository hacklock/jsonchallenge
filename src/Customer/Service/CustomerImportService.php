<?php

namespace JsonChallenge\Customer\Service;

use Carbon\Carbon;
use JsonChallenge\Customer\Exception\DuplicatedException;
use JsonChallenge\Customer\Repository\CustomerRepository;
use JsonChallenge\Reader\Factory\ReaderFactory;

class CustomerImportService
{
    private $customerRepository;

    private $readerFactory;

    public function __construct(ReaderFactory $readerFactory, CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
        $this->readerFactory = $readerFactory;
    }

    public function run(string $fileName):void
    {
        $reader = $this->readerFactory->makeFor($fileName);

        $index = 0;

        if ($this->isNotFirstTimeRunningFor($fileName))
        {
            $index = $this->customerRepository->getLastStoredIndexByFileName($fileName);
            $reader->startAt($index);
        }

        $value = $reader->read();

        while ($value)
        {
            $index++;
            $transformedData = $this->transformData($value, $index, $fileName);

            if (false == $this->passesAgeConstraint($transformedData))
            {
                $value = $reader->read();
                continue;
            }


            if (false == $this->passesCreditCardNumberConstraint($transformedData))
            {
                $value = $reader->read();
                continue;
            }

            try
            {
                $this->customerRepository->store($transformedData);
            } catch (CustomerDuplicatedException $e)
            {
            }

            $value = $reader->read();
        }
    }


    private function isNotFirstTimeRunningFor(string $fileName): bool
    {
        return $this->customerRepository->existsByFilename($fileName);
    }

    private function transformData(array $raw, int $index, string $fileName): array
    {
        $hash = $this->makeHash($raw);
        return array_merge([
            "name" => $raw["name"],
            "address" => $raw["address"],
            "checked" => (boolean)$raw["checked"],
            "description" => $raw["description"],
            "interest" => $raw["interest"],
            "date_of_birth" => $this->getDateOfBirth($raw["date_of_birth"]),
            "email" => $raw["email"],
            "account" => $raw["account"],
            "credit_card_type" => $raw["credit_card"]["type"],
            "credit_card_number" => $raw["credit_card"]["number"],
            "credit_card_name" => $raw["credit_card"]["name"],
            "credit_card_expiration_date" => $raw["credit_card"]["expirationDate"],
        ], [
            'index_in_file' => $index,
            'filename' => $fileName,
            'hash' => $hash
        ]);
    }


    private function makeHash(array $value)
    {
        return hash("sha512",
            $value['name'] . $value['address'] . $value['checked'] . $value['description'] . $value['interest'] . $value['date_of_birth'] . $value['email'] . $value['account'] . $value['credit_card']['type'] . $value['credit_card']['number'] . $value['credit_card']['name'] . $value['credit_card']['expirationDate']
        );
    }

    /**
     * @param string $dateOfBirth
     * @return null|string
     */
    private function getDateOfBirth($dateOfBirth)
    {
        if (is_null($dateOfBirth))
        {
            return null;
        }

        if ($this->dateHasSlashes($dateOfBirth))
        {
            return $this->parseSlashedDate($dateOfBirth);
        }

        return Carbon::create($dateOfBirth);
    }


    private function dateHasSlashes($dateOfBirth): bool
    {
        return (bool)strpos($dateOfBirth, '/');
    }




    private function parseSlashedDate($dateOfBirth)
    {
        return Carbon::createFromFormat('d/m/Y', $dateOfBirth);
    }

    private function passesAgeConstraint($transformedData)
    {
        if (is_null($transformedData["date_of_birth"]))
        {
            return true;
        }

        // Only process records where the age is between 18 and 65 (or unknown)

        $age = $transformedData["date_of_birth"]->diff(Carbon::now())->format("%y");

        if (18 <= $age && $age <= 65)
        {
            return true;
        }

        return false;
    }

    private function passesCreditCardNumberConstraint($transformedData)
    {
        if ($this->doesCreditCardNumberContainThreeConsecutiveSameDigits($transformedData))
        {
            return true;
        }

        return false;
    }

    private function doesCreditCardNumberContainThreeConsecutiveSameDigits($transformedData)
    {
        $creditCardDigits = str_split($transformedData["credit_card_number"]);

        $lastDigit = null;
        $sameDigitCount = 0;

        foreach ($creditCardDigits as $digit)
        {
            if (is_null($lastDigit))
            {
                $lastDigit = $digit;
                $sameDigitCount++;
            } else
            {
                if ($lastDigit === $digit)
                {
                    if ($sameDigitCount == 2)
                    {
                        return true;
                    }

                    $sameDigitCount++;
                } else
                {
                    $sameDigitCount = 1;
                    $lastDigit = $digit;
                }
            }
        }

        return false;
    }
}
