# Collections Create

Is is possible to create a totally *new* `DragonTreasure` when we create a user? Like... instead of sending the IRI of an *existing* treasure, we can send an object? Let's try it! First, I'll change this to a unique email and username. Then, for `dragonTreasures`, I'll clear those IRIs and, instead, we'll create an object and pass the fields that we already know are required for every piece of treasure. Recently, this new dragon user acquired a copy of GoldenEye for the N64. *Classic*. Below that, we'll add a `description`... and a `value`. In theory, that's a request that makes *sense*. We could make that work.

So let's see if it does. Hit "Execute" and... no surprise here. It *doesn't* work out of the box. The error says:

`Nested documents for attribute \"dragonTreasures\"
are not allowed. Use IRIs instead.`

That sounds familiar. Inside `User.php`, if we scroll *way* up, the `$dragonTreasures` field *is* writeable because it has `user:write`. But we can't send an embedded object because we haven't added `user:write` to any of the fields *inside* of `DragonTreasure`. Let's fix that.

We want to be able to send `$name`, so we'll add `user:write` up here. I'll skip `$description` for a minute. Then we can do the same thing for `$value`. Now we need to search for `$setTextDescription` which is our *actual* description, and add `user:write` there too.

Okay, *in theory*, we should be able to send an embedded object now. If we head over and try this again... we *upgraded*... to a 500 error. This one's familiar too.

`A new entity was found through the relationship 'App\Entity\User#dragonTreasures'`

This is good! Earlier, we learned that when you send an embedded object, if you include an `@id`, it's going to fetch that object first and then update it. But if you don't have an `@id`, it's going to create a brand new object. Right now, it *is* creating a brand new object, but nothing told the entity manager to *persist* this new object. That's why we're getting this error.

To solve this, we need to *cascade* persist this. That means, in `User.php`, on the `OneToMany` for `$dragonTreasures`, we need to add a `cascade` option set to `['persist']`. This means if we're saving this user object, it should automatically persist any `$dragonTreasures` inside of here. And if we try it now... it works! That's awesome! And apparently, our new `id` is `43`. If I go open up a new browser tab and navigate to that URL, plus `.json`... actually, let's do `.jsonld`... beautiful! You can see the `owner` is set to the new owner that we just created.

But... wait a second. We didn't *send* the `owner` field inside of our `DragonTreasure`. It makes *sense* that we didn't send it, since we don't even have the `id` yet so it isn't necessary. But what *did* set that? Behind the scenes, the serializer is going to create a new user object *first*. *Then*, it's going to create a new `DragonTreasure` object. It will see that this `DragonTreasure` doesn't exist on that user yet, so it will call `addDragonTreasure()`. When it does that, this code down here sets the `owner`. So our code being written well is taking care of all of those details *for* us. Awesome!

Okay, I *could* make the email unique one more time and send an empty `name `, but I'm going to save myself to trouble. If I sent this, it *would* create a `DragonTreasure` with an empty `name`, even though, over here, if we scroll up to the `name` property, we can see that it's *required*. Why? This is the same thing we saw on the other side of the relationship. When the system validates the user object, it's going to stop at `$dragonTreasures`. It won't validate those. If you *want* to validate them, we need to add `#[Assert\Valid]` right here.

Now that I have this, to prove that it's working, I'll hit "Execute" and... awesome! We get a  422 status code telling us that `name` shouldn't be empty. Awesome. I'll go put that back. So now we know that we can send IRI strings *or* embedded objects to *create* objects. We can even *mix* them.

Let's say that we want to create this new `DragonTreasure` object, but we're also going to *steal* a treasure from another dragon. That's *totally* allowed. Watch! When we hit "Execute"... we get a 201 status code. We have `id` `44` (that's the new one), and we also have `id` `7`, which is the one we just stole from another dragon. Pretty sweet!

Okay, we only have one more chapter about handling relationships. Let's see how we *remove* a treasure from a user to *delete* that treasure. That's *next*.
