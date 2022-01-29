<?php

namespace JsonChallenge\Customer\Repository;

use JsonChallenge\Customer\Entity\Customer;
use JsonChallenge\Customer\Exception\DuplicatedException;

class CustomerRepository
{
    public function existsByFilename(string $fileName):bool
    {
        return Customer::where("filename", $fileName)->exists();
    }

    public function getLastStoredIndexByFileName(string $fileName):int
    {
        return Customer::where(["filename" => $fileName])->max("index_in_file");
    }

    public function store(array $transformedData):Customer
    {
        $customer = new Customer($transformedData);

        try
        {
            $customer->save();
        }
        catch(\PDOException $e)
        {
            if(strpos($e->getMessage(), 'Duplicate entry'))
            {
                throw new CustomerDuplicatedException;
            }

            throw $e;
        }

        return $customer;
    }
}
