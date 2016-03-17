<?php

namespace Langsyne\Validators;

class NullValidator implements ValidatorInterface {

    public function getProfile() {
        return 'http://null.org/#null';
    }

    public function validate(array $data) {
        // do nothing
    }

}
