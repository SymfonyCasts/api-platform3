# Write Relation

Open up `DragonTreasureResourceTest` and check out this `testPostToCreateTreasureWithLogin()`. We've already talked about making our endpoints *return* relations, and we've set up the data mapper so that we can create these relationships. One thing we *haven't* talked about is being able to *write* to one of these relationships.

Right now, when we use this `post()` endpoint, we don't *need* to send an `owner` field here. That's because, in our `DragonTreasureApiToEntityMapper`, we have some logic that says

`Hey! If an owner is not sent on the DTO...`

like this case here,

`then automatically set the owner and entity to
the currently authenticated user.`

*But*, you *are* allowed to send the `owner` property and set it to *yourself*. Let's try that. Say `'/api/users/'.$user->getId()`. When we do that, it *should* hit *this* part of our test. So... let's test it out! At your terminal, run:

```terminal
symfony php bin/phpunit
```

followed by *just* that test. And... perfect! It *hits*! And check this out! It's a `UserApi` object. This is *really* important. Let's dump *just* the `$dto` so we can see things in more detail. Okay... *awesome*. Most of the time, behind the scenes, when we send this JSON data, the serializer *deserializes* all of this into a `DragonTreasureApi` object. This string goes onto the `name` property, *this* string goes onto the `description` property, and so on. Over here, we can see that... string... string... 1,000... and 5. *Super simple*. But something special happens when the field you're sending is a *relationship* field. This IRI is actually *transformed* into the `UserApi` object. How does it do that? The *answer* is the *state provider*.

So far, the only time that the state provider is used (as far as we know) is when we're actually *fetching* that resource. If we fetch a user here or here, or if we `PATCH` or `DELETE` a user, it's going to look up that user or *users* with the state provider. *But* there's one *other* spot where a state provider is used, and that's when you *send* an IRI on a relationship field in a `post()` or `patch()` request.

During the deserialization process, this IRI is taken, *sees* that it's for a `UserApi` object, and then it calls the state provider to find *that*, and whatever the state provider returns is ultimately what is set onto the `owner` property of our `DragonTreasureApi`. It's just cool to see how that's working behind the scenes.

Anyway, in our mapper, our job is pretty straightforward. We know that `$dto->owner` is going to be a `UserApi` object. What we *ultimately* need is a `User` entity. So, once again, we're going to use the mapping system to go from `UserApi` over to `User`. Up here, let's inject `MicroMapperInterface $microMapper`. Cool. And down here, we'll say `$entity->setOwner()`, but *this time*, we're going to use `$this->microMapper->map()` to go from `$dto->owner` to `User::class`. And remember, any time we map a relationship, we should *probably* add a `MAX_DEPTH` as well, so say `MicroMapperInterface::MAX_DEPTH => 0`. We only need `0` here because this is just going to query for a `User` object. We don't need to map it to a *deeper* level. We would only need to do that if we were allowing this to be an embedded object, like creating a new one on the fly. *Or* if we were doing something crazy like adding the `@id` and then modifying it.

These are things we talked about in previous tutorials. They *are* possible, but if we actually *did* try to do this, API Platform wouldn't allow it. Remember, you can only *write* embedded data to an object if you have the serialization groups set up correctly. We *don't* have that at the moment, so this isn't even allowed. So that's good to know, but it's *not* something that we need to worry about right now. The only thing we're concerned about is making sure that we're loading the correct `User` entity object here. If we run our test again... it's *good*. We are *now* allowed to *write* owners. And we also understand a bit more about how the IRI becomes the `$dto` object inside of the system.

Next: Let's shift our focus to setting a `DragonTreasure` onto a `User`. Adding or removing them is a *collection* property, and that will require an extra trick.
