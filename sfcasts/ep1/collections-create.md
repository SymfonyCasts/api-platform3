# Collections Create

Coming soon...

I wonder if we could create a totally new dragon treasure when creating a user. Like
instead of sending the i r I of an existing treasure, we'll send an object. So let's
try this first, let me get uh, a unique email and username and then here for Dragon
Treasure I'll clear out those Iris and instead we'll do an object and we'll pass the
fields that we know are required on every piece of treasure. So we'll top this new
user found golden eye from N 64. Add a description and a value. So in theory, that's
a con request that makes sense, like that we could make that work. So let's try it.
Hit execute and okay, no big surprise, it doesn't work. Out of the box, it says
nested documents for attribute. Dragon treasures are not allowed. Use Iris instead.
That's a familiar error.

<affirmative>

Inside user. If we scroll way up, the dragon treasures field is writeable because it
has user colon, right? But we can't send in an embedded object because we haven't
added user colon right to any of the fields inside of Dragon Treasure. So let's do
that. We wanna be able to send name. So let user colon right there. Uh, description,
actually not description. Wait on that one for a second. Value. And then search for
set Text description. This is our actual description. Field slide, user colon right
there. Now in theory we should be able to send an embedded object. Let's give it a
drive. This time we upgraded to a 500 air. Also a familiar error. A new entity was
found through the relationship user. Dragon treasures. So this is good. It means that
we learned earlier when you send it an embedded object,

If you include an ad id, it's going to fetch that object first and then update it.
But if you don't have an ad id, it's going to create a brand new object. So it is
creating a brand new object, but nobody ever told the entity manager to persist this
new object, which is why we have the error down here. If you wanna solve this, we
need to allow ca, we need to add ca, we need to cascade persist this. So what that
means is in user, on the drag, on the one to many for Dragon treasures, we need to
add a cascade option set to an array with persist on it. So this means if we're
saving this user object automatically persist any dragon treasures inside of here and
now it works. That's awesome. Now apparently our new ID is slash is 43. That's
actually, I'll open up a new, a new browser. Go to that U URL dot json. Actually
let's do do Jason LD and beautiful. And you can see the owner is set to our new owner
that was just created over here. But wait a second. We didn't send, send the owner
field inside of our dragon treasure. And it makes sense that we didn't send it. We
don't even have the ID yet and we shouldn't need to send it. But who did set that?
Well remember

Because this is a behind the scenes, the serializers first going to create a new user
object. It's then gonna create a new dragon treasure object. It's gonna see that this
dragon treasure doesn't exist on that user yet. And so it's gonna call add dragon
treasure and we call add dragon treasure. With that new dragon treasure. This code
down here sets the owner. So again, just our code being written well is taking care
of all those details for us. Awesome. So let me change the, make the email unique one
more time.

But then I'm going to send an empty name and actually I'm gonna save myself to
trouble. I'm not even gonna send this. If I sent this, that would work. It would
create a dragon treasure with an empty name. Even though over here, if we scroll up
to the name property, the name is required. Why? It's the same thing we saw on the
other side of the relationship. When we validate when this isn't validates the user
object, it's gonna stop at this dragon treasures. It's not gonna validate those. If
you wanna validate them, we need that same valid on there. So now that I have this to
prove it's working, I'll hit execute and awesome 4 22 status code. Failing that, that
it is empty and I guess I could probably change this validation error to awesome. I'm
gonna go put that back. So pretty simply, you can send an I r I strings or you can
send embedded objects now to create objects. You can even mix them. So let's say that
we wanna create this new dragon treasure object, but we also are going to steal. I
think that's an id. Another treasure from someone else

That's totally allowed. Watch. We hit execute 2 0 1 status code. We have ID 44,
that's the new one. And we have ID seven, the one we just stole from another dragon.
Pretty sweet. Okay, we have just one more chapter about handling relationships. Let's
see how we can remove a treasure from a user to delete that treasure.

