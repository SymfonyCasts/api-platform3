# Entity Item Provider

Coming soon...

Alright, what about the item endpoint? So we've got the collection endpoint working,
but let's see here. Let's go to //users/.jsonlod in my case. And it looks like it
works, but this is actually just the collection endpoint returning a single item. As
we saw earlier, there are actually two different core providers, not just one.
There's the collection provider, which is used on the collection endpoint, and
there's also an item provider, whose job is to return one item or null. That item
provider is what's used for the get one endpoint, the put endpoint, patch endpoint,
and also the delete endpoint. Right now, because we have set the entity to
DTOStateProvider, it's using this one provider for all of those operations, which
means it's using the collection provider. So no worries, we could create two separate
providers, but I actually like to combine them all into one for simplicity. So we saw
how to do this earlier. This operation is going to be the key. We can say, if
operation is an instance of collection operation interface, then we are dangerous. We
can just wrap all of that in there. Perfect. And then down here, this will be our
item interface. I'm going to DD that URI variables argument, which we also did
earlier. And if we go over and try the item operation, perfect. That's what we
expect. So we have the ID is kind of the dynamic part of our route. And so that
passes us the ID. And that's what we can use. Now down here, our job, just like with
the collection, we're not going to do the querying work manually, we're just going to
offload that to the core, the core doctrine item provider. So up here, let's add a
second argument. In fact, I'll copy the first argument, we'll call it item provider,
the one from doctrine ORM. And this time, I'll call it item provider. Perfect. And
down here, things just get easy. So I'll say entity equals this arrow item provider
arrow provide, we'll pass the arguments that needs which is operation URI variables
and context. And this will give us either an entity object or null. So I'm gonna say,
if we don't have an entity object, let's just return null, that's going to trigger a
404. But if we do have an entity object, we don't actually want to return that
directly. Because remember, the whole point of this class is to take the entity
object and transform it into our user API DTO. So instead, we're going to return this
arrow map entity to DTO and pass that our entity. And boom, we are returning a user
API object. And the endpoint works beautifully. And if we try something that is
invalid, our provider returns null API platform takes care of doing the 404 for us.
Also by the way, if you follow some of these related treasures, they may 404. So
let's see we have 21 and 27. 21 works for me. How about 27? That 27 also works for
me. So these both work for me. But the reason they might 404 is that right now, if I
go back, these dragon treasures include all of the treasures related to this user,
even the unpublished ones. But we have logic from our previous tutorial that will
actually make unpublished dragon treasures return a 404. They won't be found thanks
to this query extension. But anyways, originally, when we had our user entity as a as
our API resource, we didn't return all of the treasures on that endpoint, we created
a new get published dragon treasures, which only returns the published treasures in
this is actually what we returned on the dragon treasures property. Right now in our
state provider, you can see we're actually returning all of them. So this is an easy
fix. We're just going to change this to get published. Dragon treasures. And now
let's see what's any difference on this, let me actually go back to the collection
endpoint here. Actually going to undo that, let's see if we can see a difference
here. Ah, I see it 16 and 40 down there, I redo it and just show the published ones.
40 is gone for years unpublished. So that was really easy to do. But it highlights a
really cool thing before. In order to have this dragon treasures field kind of return
something different, we had to have like a serialized name on top of here, we had to
have this dedicated method. So we had this API property called dragon treasures that
looked different than our actual entity property. As soon as we have this custom
class, we don't need any weirdness at all, all of the kind of weirdness is handled in
our state provider. When we're transferring the data, we can grab whatever data we
want, and just put it on to our DTO. Our DTO just looks as simple as it could
possibly be. There's nothing weird about it at all. And I really like that cleanness.
All right, next up, let's get our user saving with a state processor. But we're still
going to offload almost all of the work to the core doctrine state processor.
