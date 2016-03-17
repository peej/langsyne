<?php

namespace Langsyne\Validators;

interface ValidatorInterface {

    public function getProfile();

    public function validate(array $data);

}
