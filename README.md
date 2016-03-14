# Langsyne

A base for building REST APIs on Slim3

## Usage example

```
$app = new \Slim\App();

$c = $app->getContainer();

$c['datastore.items'] = function ($c) {
    return new Langsyne\DataStores\FileSystemDataStore();
};

$c['renderer'] = function ($c) {
    return new Langsyne\Renderers\JsonRenderer();
};

$c['validator.item'] = function ($c) {
    return new Langsyne\Validators\NullValidator();
};

$itemCollection = new Langsyne\Resources\HttpCollectionResource(
    $c['datastore.items'], $c['renderer'], $c['validator.item'], $c['router'], 'item'
);
$itemResource = new Langsyne\Resources\HttpResource($c['datastore.items'], $c['renderer'], $c['validator.item']);

$app->any('/items', $itemCollection)->setName('items');
$app->any('/items/{id}', $itemResource)->setName('item');
$app->any('/users/{name}/items/{id}', $itemResource)->setName('user-item');

$app->run();
```