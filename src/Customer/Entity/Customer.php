<?php

namespace JsonChallenge\Customer\Entity;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        "id",
        "hash",
        "index_in_file",
        "filename",
        "name",
        "address",
        "checked",
        "description",
        "interest",
        "date_of_birth",
        "email",
        "account",
        "credit_card_type",
        "credit_card_number",
        "credit_card_name",
        "credit_card_expiration_date",
    ];
}
