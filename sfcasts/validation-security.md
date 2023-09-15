# Validation Security

Let's talk about *validation*. When we `->post()` to our endpoint, the internal object will now be our `UserApi` object, and *that's* what's going to be validated. For example, if we send *no* fields to our `POST` request and *run* that test... it fails with a 500 error, and I bet you can guess why. This says

`User::setEmail(): Argument #1 ($email) must be of
type string`

which is coming from our state processor on line 59. So, since there are no validation constraints *at all* in our `UserApi`, our email remains `null`. And over here on line 59, we try to transfer a null `email` onto our `$entity`. It doesn't like that, and even if it *did*, this would eventually *fail* in the database because the email is not supposed to be empty. We're missing *validation*, and that's pretty easy to add. The *point* is that validation is happening on our `UserApi` class now.

Before we get into validation, let's specify our `operations` so we'll only have the ones we want. Say `new Get()`, `new GetCollection()`, and `new Post()`. Down here, we also want to say `new Patch()` and `new Delete()`. Originally, on our `User` entity (when that was part of our API), our `Post()` operation had an extra `validationContext` with `groups` set to `Default`, and then `postValidation`. This means that when the `Post()` operation happens, it's going to run *all* of the validators in the `Default` group, which are all the *normal* validators, *plus* any that are in this `postValidation` group. I'll show you where this comes into play in a moment, but we're basically just repeating code from an earlier tutorial. This code *used to* live over on our `User` entity.

Down here... `$id` isn't even writable... we want `$email` to be `#[NotBlank]`... and we also want it to be an `#[Email]`. We want `$username` to be `#[NotBlank]`... and then `$password` is an interesting one. This `$password` should be *allowed* to be blank when we're doing a `PATCH` request, but *not* on a `POST` request. *That's* where we'll say `#[NotBlank]`, but we'll *also* pass `groups` set to `postValidation` so this will only be run when we're validating the `postValidation` group. That means this will only be run when we're doing the `Post()` operation. Okay, that should be it! If we run the test now... beautiful! We get a 422 status code. That's the validation error, and *that's* what we wanted.

One thing I want to note here is that, back when we had this on the `User` entity, one of the other validation constraints we had was a `#[UniqueEntity]`. That basically makes sure that we don't try to create *two* users with the same email or username. We don't currently have that on our `UserApi`, but we *should*. This `#[UniqueEntity]`, as the name would suggest, only works on *entities*, so we'd actually need to have custom validation. We would need to create a custom validator to add that logic for our `UserApi`. We're not going to worry about that right now, but I wanted to point that out. Let's go back over here and re-add our fields. Cool.

So we *have* validation. The next thing we need to re-add - code that *used to* live on `User` - is *security*. Up here on the API level, for the *entire* operation by default, we're requiring `is_granted("ROLE_USER")`. This basically means that we need to be logged in in order to use *any* of the operations for this resource *by default*. Then we *overrode* that - first in the `Post()`, because for the `Post()`, we definitely can't be logged in yet because we're actually registering our user. Here, we can say `security`, and we can set that to `is_granted("PUBLIC_ACCESS")` which is a special attribute that will always pass.

Then, down here for `Patch()`, we had `security("is_granted("ROLE_USER_EDIT")`. So, in our API, we've made it so that we can only modify a user if we have some kind of special role that allows us to modify them. You might set this up differently in your own application, but we're just repeating what we set up in the previous tutorials for our `User` entity.

Okay, now let's run *all* of the tests for our user:

```terminal
symfony php bin/phpunit tests/Functional/
UserResourceTest.php
```

And... *oh*. Not bad! We got three out of four, so we just have *one* failure. It's on a method called `testTreasuresCannotBeStolen()`. If we check that out... this is a really interesting test where we `->patch()` to update a `$user`, and then we try to set the `dragonTreasures` to the treasure of a *different* user. You can see that this `$dragonTreasure` is owned by `$otherUser`, but we're currently updating `$user`. So, basically, what this is attempting to do is *steal* this `$dragonTreasure` from `$otherUser` and make it part of `$user`. And you can see that we're asserting that this is a 422 status code. *Previously*, we had a custom validator, and we actually still have it. It's this `TreasuresAllowedOwnerChangeValidator.php`, but it's *not* being applied to our `UserApi` and it needs to be *updated* to work with it. That's something we're going to worry about later, but for now, I just wanted to mention it.

Even *more* important, right now, the `dragonTreasures` property isn't even *writable*. In `UserApi.php`, above `$dragonTreasures`, we have that as `writable: false`. In a little bit, we're going to change that so we can write `$dragonTreasures` again, and when we do that, we'll bring back that validator and make sure this test passes. Aside from this *one* test, the rest of our user stuff *passes*.

Next: If you look at the processor *or* the provider we created, these classes are pretty generic. They could *almost* work for `UserApi.php` or even a future "DragonTreasure" API class. The only code that's *specific* to `$user` is the code that maps *to* and *from* the `User` entity and the `UserApi` class. That means the missing piece to make this *generic* is some kind of *mapping* system to do that conversion outside of this class. Let's add that!
