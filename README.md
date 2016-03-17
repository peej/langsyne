# Langsyne

A base for building REST APIs on Slim3

## Usage example

```
$app = new \Langsyne\App();

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

$homeResource = new Langsyne\Resources\HttpResource();
$app->addResource('home', '/', $homeResource)
    ->addData("message", "Welcome")
    ->addLink("items")
    ->addLink("item");

$itemCollection = new Langsyne\Resources\HttpCollectionResource('item', $c['datastore.items'], $c['validator.item']);
$app->addResource('items', '/items', $itemCollection);

$itemResource = new Langsyne\Resources\HttpResource($c['datastore.items'], $c['validator.item']);
$app->addResource('item', '/items/{id}', $itemResource)
    ->addLink("home", "up");

$app->run();
```