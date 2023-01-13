# Collections Remove Item

Coming soon...

We just created a new user that now has these two treasures, IDs seven and 44. Let's
update this user to see if we can to play with those dragon treasures on it. So I'll
use the put endpoint. I try it out. I already forgot the ID and the new user. 14. So
I'll put 14 in here and I'm gonna remove all the fields except for dragon treasure.
So we know that it has two dragon treasures currently slash api slash treasure slash
seven and slash api slash treasure slash 44. So if we sent, if we sent this request
here in theory, that should do nothing and in fact, move right down here. Yeah, it
makes no changes at all. So now let's add, let's suppose that we want to be able to
add a new dragon treasure to this resource. So we're gonna list the two that it has
and let's say API slash treasures slash treasurers slash eight. I'm guessing that's a
valid id. So we're gonna steal that dragon treasure from a different dragon and when
we hit execute, that works beautifully. The serializer system noticed that it already
had these first two, so it did nothing.

So it just added this one new one. But what I really wanna talk about is removing
one. So let's say that our dragon lost one of these tres, so we wanna remove one. So
we're gonna mention the two that he has, and then we're gonna delete one of them.
When we execute, it explodes 500 air, an exception occurred, not null violation, null
value, and owner id. So what's happening here is that Dragon Treasurer that I just
removed, our app set its owner property to null and is trying to save it, but
dragons, but since we haven't nullable false, it's failing. So let me back up here.
What happens? What the serializer did was it noticed that seven and eight are already
treasures it had, but that the other treasure 44 was removed. And so over on our user
class, it called Remove dragon treasure. And what's really important is that took
that dragon treasure and it set the owner to Noel to break the relationship. Now,
depending on your app, that might be exactly what you want. Maybe you allow dragon
treasures to have no owner because they're now undiscovered and waiting for a dragon
to find them. So if that's the case, you would just make sure that your relationship
allows Noel, then this would save just fine. But in our case, if a dragon treasurer
no longer has an owner, we want to delete that dragon treasure.

The way we do that is in user way up on our dragon treasure treasure's property after
our cascade. We're gonna add one more option here called orphan removal. True. This
says that if any of these dragon treasures become orphaned, they should actually be
removed. They should be deleted. So now if this change, if we execute again, got it.
It saves just fine. And if you look that treasure with the ID 44 is now totally
deleted from the system. All right, next, let's circle back to filters and how we can
use them to search across related resources.

