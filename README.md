# Setup

```
git clone git@github.com:SymfonyCasts/api-platform3.git testing_iri_state_options
cd testing_iri_state_options
git checkout -b playing-with-stateOptions origin/playing-with-stateOptions
composer install
symfony serve
```

Then go to https://localhost:8000/api/user_apis.jsonld - you should see the collection.

To trigger the problem, uncomment-recommend the `id` property type and how it's set:

A) In `src/State/UserApiStateProvider.php`

```diff
diff --git a/src/ApiResource/UserApi.php b/src/ApiResource/UserApi.php
index dcbbc70..de389bc 100644
--- a/src/ApiResource/UserApi.php
+++ b/src/ApiResource/UserApi.php
@@ -24,11 +24,11 @@ use Doctrine\Common\Collections\Collection;
 class UserApi
 {
     #[ApiProperty(identifier: true)]
-    public User|null $id = null;
+    //public User|null $id = null;

     // will not work
     // (change line also in UserApiStateProvider)
-    // public ?int $id = null;
+    public ?int $id = null;

     public ?string $email = null;
```

B) In `src/ApiResource/UserApi.php`:

```diff
diff --git a/src/State/UserApiStateProvider.php b/src/State/UserApiStateProvider.php
index 4534c13..6de0339 100644
--- a/src/State/UserApiStateProvider.php
+++ b/src/State/UserApiStateProvider.php
@@ -27,11 +27,11 @@ class UserApiStateProvider implements ProviderInterface
             $userApi = new UserApi();

             // works
-            $userApi->id = $user;
+            //$userApi->id = $user;

             // will not work
             // change line also in UserApi
-            //$userApi->id = $user->getId();
+            $userApi->id = $user->getId();

             $userApi->email = $user->getEmail();
             $userApi->username = $user->getUsername();
```

Refresh to see:

> Unable to generate an IRI for the item of type "App\ApiResource\UserApi
