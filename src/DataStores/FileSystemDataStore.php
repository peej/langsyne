<?php

namespace Langsyne\DataStores;

class FileSystemDataStore implements DataStoreInterface {

    private function buildKey(array $keys) {
        if (isset($keys['id'])) {
            $id = $keys['id'];
            unset($keys['id']);
        } else {
            $id = null;
        }
        return [$id, (string)join('-', $keys)];
    }

    private function getFilename($key) {
        if (!$key) {
            $key = 'default';
        }

        return sys_get_temp_dir().'/'.$key;
    }

    private function loadDataStore($key) {
        if (!file_exists($this->getFilename($key))) {
            return [];
        }

        return json_decode(file_get_contents($this->getFilename($key)), true);
    }

    private function saveDataStore($key, $data) {
        file_put_contents($this->getFilename($key), json_encode($data));
    }

    public function listing(array $keys, $page = 1, $pageSize = 10) {
        list($id, $key) = $this->buildKey($keys);
        return $this->loadDataStore($key);
    }

    public function read(array $keys) {
        list($id, $key) = $this->buildKey($keys);
        $dataStore = $this->loadDataStore($key);

        if (!isset($dataStore[$id])) {
            throw new DataNotFoundException('Not found');
        }

        return $dataStore[$id];
    }

    public function write(array $keys, array $data) {
        list($id, $key) = $this->buildKey($keys);
        $dataStore = $this->loadDataStore($key);
        $dataStore[$id] = $data;
        $this->saveDataStore($key, $dataStore);
    }

    public function create(array $keys, array $data) {
        list($id, $key) = $this->buildKey($keys);
        $dataStore = $this->loadDataStore($key);
        $dataStore[] = $data;
        $this->saveDataStore($key, $dataStore);
        
        end($dataStore);
        $keys['id'] = key($dataStore);

        return $keys;
    }

    public function remove(array $keys) {
        list($id, $key) = $this->buildKey($keys);
        $dataStore = $this->loadDataStore($key);
        unset($dataStore[$id]);
        $this->saveDataStore($key, $dataStore);
    }
}
