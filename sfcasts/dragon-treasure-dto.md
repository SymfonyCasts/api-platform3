# Dragon Treasure Dto

It's time to convert our `DragonTreasure` `ApiResource` into a *proper* new DTO class! We're going to start by deleting a *ton* of stuff. We'll delete everything related to API Platform on `DragonTreasure` so we have a clean slate to work with, and then we'll add in what we need little by little. That just makes things easier to work with. Let's get rid of all the filter stuff... the validators... all of the serialization group stuff... and then we can do some cleanup on all of our properties. You can that we have some fairly complex stuff here. We won't add *all* of this stuff back, but we *will* add the most important things and see how it looks inside of our new system. We'll scroll down and make sure we got everything, and... that should be it. Cool!

*Now* we have a good old fashioned, *boring* entity class. We'll also remove a few other files here. In `/ApiPlatform`, get rid of `AdminGroupsContextBuilder.php`. This was a complex way to make `groups` fields readable and writable by your admin, but we're going to solve that with API Property security. We'll also get rid of this custom normalizer, which added a custom field and an extra group. Finally, we'll get rid of the custom `DragonTreasureStateProvider.php` and `DragonTreasureStateProcessor.php`. All of our custom code will be added in a different way.

You may have noticed that one thing we *did* keep was `DragonTreasureIsPublishedExtension.php`. Since the new system is *still* going to use the core Doctrine collection provider, this query extension stuff will *continue* to work and be called automatically. That's cool, and it's one less thing we need to worry about.

If we go over and refresh the documentation... we only have Quest and User. Though, you'll notice some `DragonTreasure` stuff down here. That's because, right now, our `UserApi` is still referencing the `DragonTreasure` entity. So even though `DragonTreasure` isn't an API resource, it's still trying to document it because it's going to be included in the API. We're going to fix that and *completely* use our API classes everywhere.

In `/ApiResource`, let's create a new class - `DragonTreasureApi`. Now, in `UserApi.php`, we're going to steal some of the basic code from our API resource. Paste that over here, and we're actually going to delete the operations for now. We can also get rid of these `use` statements. Perfect! We *will* use a `shortName` - `Treasure` - give this `10` items per page, and remove the `security` line. The *most* important thing is that we have `provider` and `processor` (just as they are here), and `stateOptions`, which is pointing to `DragonTreasure::class`. Beautiful! To start, we're also going to grab this `?int $id`. Just like before, we don't actually want that to be part of our API. So it's `readable: false` *and* `writable: false`. Down here, add `public ?string $name = null`. And that's it!

We have this one tiny class, so let's go try it and see what happens. If we refresh the API... beautiful! Our Treasure endpoint is there. If we try the collection endpoint... we get:

`No mapper found for App\Entity\DragonTreasure ->
App\ApiResource\DragonTreasureApi`

This is great! The only real work that we need to do is implement those mappers, so let's do it! Open this `/Mapper` directory and create a class called `DragonTreasureEntityToApiMapper`. We've done the next part before. Let's implement `MapperInterface` and add the `#[AsMapper()]` attribute. We're going `from: DragonTreasure::class` `to: DragonTreasureApi::class`. The `MicroMapper` should use *this* now. We'll also generate the two methods that it needs: `load()` and `populate()`. For simplicity's sake, we'll say `$entity = $from`, and then `assert()` that `$entity` is an `instanceof DragonTreasure`. *Cool*.

Down here, we need to create a new DTO object, so say `$dto = new DragonTreasureApi()`. And remember, the job of `load()` is to create the object and put an identifier on it if there is one, so we'll say `$dto->id = $entity->getId()`. Finally, `return $dto`.

We'll do the same thing for `populate()`, and we can actually steal a few lines from above that set the `$entity` variable. Say `$dto = $to`, and we'll add one more `assert()` that `$dto` is an `instanceof DragonTreasureApi`. And the only property that we have on our DTO right now is `name`, so say `$dto->name = $entity->getName()`. At the end, `return $dto`. Boom! We've just created a class that helps us map from the entity to the DTO, and our state provider is using `MicroMapper` internally, so it should just use *that*. And... it *does*! So with *just* the API Resource class and this *one* mapper, we now have a database-powered *custom* API Resource class. Woo!

*Now* things can get a little more *interesting*. Every `DragonTreasure` has has an *owner*, which is a *relationship* to a user. In our API, we're going to have the same relationship, but instead of being related to the user entity, we're going to have it related to the `UserApi`. Check this out! We'll say `public ?UserApi $owner = null`, and *now*, our job is to populate that over in our API mapper. So down here, say `$dto->owner =`... but... hold on a second. This isn't as simple as saying `$entity->getOwner()`, because that's a *user entity object*. What we *really* need is a `UserApi` object. This is really cool! We're going to use the `MicroMapper` to convert this user entity object into a `UserApi` object, and we can do that because we *already* have a mapper defined for that.

Up here on top, we're going to inject `private MicroMapperInterface $microMapper`... and, down here, we'll say `$dto->owner = $this->microMapper->map()`. We'll map `$entity->getOwner()`, which is that user entity object, to `UserApi::class`. How cool is that?

One thing to be aware of is that if, in *your* system, `$entity->getOwner()` might be `null`, then you'll want to code defensively here. What do I mean by that? Well... you would want to say something like

`if you have an owner, then map it.
Else, pass null.`

Or maybe you don't set the owner *at all* if it's null. In *our* case, we're *always* going to have an owner, so this should be safe.

Okay, let's refresh, and... *oooh*. Look at that! We have an owner field and it's showing up as an IRI. Why *is* that showing up as an IRI? Because API Platform recognizes that the `UserApi` object is an API *resource*. And how does it show API resources that are relations? By default, it sets them as the IRI. So that's *exactly* what we wanted to see.

All right, let's fill in the rest of the fields here. I'll go through this super fast. One of the fields we're creating is called `$shortDescription`. That's actually a *custom* field that was in our old API. It's *much* simpler now. Another custom field we had was `$isMine`. That's just going to be a normal property, and we'll see how that's defined in a moment.

Over in our mapper, we just need to set everything. I'll speed through this too. Most of it is normal, but the `$shortDescription` is a little different. Before, in `DragonTreasure.php`, we had a `getShortDescription()` method. We actually had *this* as a custom API property, calling that getter. This time, we're going to simplify it. This is going to be a normal property like anything else, and we'll handle setting the custom data in our mapper. *So* the `$shortDescription` is equal to `$entity->getShortDescription()`. And *finally*, for `$dto->isMine`, which is for if the currently authenticated user *owns* this, we'll temporarily hard code that to `true`. If we go over and refresh now... ah! That's *beautiful*! And to show you *why*, let's try one of our tests.

In `/tests/Functional`, we have `DragonTreasureResourceTest.php`. In *here*, you can see `testGetCollectionOfTreasures()`. This tests to make sure that we only see *published* items. This is going to *guarantee* that our `isPublished` extension still working. It also checks to make sure we have all of the correct keys. *So*, over in your terminal, say:

```terminal
symfony php bin/phpunit --filter =testGetCollectionOfTreasures
```

Look at that! It passes *immediately*. This is super exciting!

Okay, before we finish, let's actually fix the hard coded `true` on `isMine`. This is easy, but it shows off just how nice it is to work with custom fields. In our mapper, this is a *service*, so we can inject *other* services like the `$security` service. *Then*, we can just populate that with whatever data we want down here. So `isMine` is equal to `$this->security->getUser()`. We're saying if there *is* a currently authenticated user, and *if* that user equals the `DragonTreasure` `getOwner()` (which is a user object), then it's *ours*. We'll run the test one more time to make sure this is working, and... it *is*. Woo!

Next: I want to dive a little deeper into the idea of having relationships in our API. It's a problem with a cool solution, but it can also cause *recursion* if you're not careful.
