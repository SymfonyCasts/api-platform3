# Setup

```
git clone git@github.com:SymfonyCasts/api-platform3.git testing_iri_state_options
cd testing_iri_state_options
git checkout -b playing-with-stateOptions origin/playing-with-stateOptions
composer install
symfony serve
```

Then go to https://localhost:8000/api/user_apis.jsonld - you should see the collection.

To trigger the problem, comment-out the `provider` option in `UserApi`.
This will result in a "standard" `stateOptions` setup, where the core entity provider
is used and return entity objects.

```diff
--- a/src/ApiResource/UserApi.php
+++ b/src/ApiResource/UserApi.php
@@ -16,7 +16,7 @@ use Doctrine\Common\Collections\Collection;
 #[ApiResource(
     paginationItemsPerPage: 5,
     stateOptions: new Options(entityClass: User::class),
-    provider: UserApiStateProvider::class
+    //provider: UserApiStateProvider::class
 )]
 #[ApiFilter(SearchFilter::class, properties: [
     'username' => 'partial',
```

Refresh to see:

> Unable to generate an IRI for the item of type "App\ApiResource\UserApi
