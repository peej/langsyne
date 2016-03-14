<?php

namespace Langsyne\DataStores;

interface DataStoreInterface {

    public function listing(array $keys, $page = 1, $pageSize = 10);
    public function read(array $keys);
    public function write(array $keys, array $data);
    public function create(array $keys, array $data);
    public function remove(array $keys);

}
