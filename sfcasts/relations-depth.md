# Dtos, Mapping & Max Depth of Relations

Before we keep going, head to `/api/users.jsonld` to see... a circular reference
coming from the serializer. Yikes!

Ok, let's think: API Platform is serializing whatever we're returning from the
state provider. So head there.... and find where the collection is created. Dump
the DTOs: these are what's being serialized, so the problem must be here.

Ok, refresh and... no surprise: we see 5 `UserApi` objects. Ah, but *here's* the
problem: the `dragonTreasures` field holds an array of `DragonTreasure` *entity*
objects... and each one has an `owner` that points to a `User` entity... and
that points *back* to a collection of `DragonTreasure` entities... which causes
the serializer to serializer forever and ever. 

But that's not even the *real* problem. I know, I'm full of good news. The actual
problem is that the `UserApi` object should *really* relate to a `DragonTreasureApi`,
not a `DragonTreasure` entity. If we're using DTOs for our API... we should use
DTOs everywhere and consistently!

So let's fix that. So over in user API, this
will now be an array of Dragon Treasure API. This is the thing I talked about where
it works best once you start going this DTO route to make everything a DTO and have
all your DTOs relate to DTOs instead of mixing DTOs and entities. Now we need to
go to our mapper to fix this. So this is going to be our user entity to API mapper.
So down here for Dragon Treasure, so we can't do this anymore because that's going
to be Dragon Treasure entity objects. And just like we saw a second ago, what we
basically want to do is convert from Dragon Treasure over to Dragon Treasure API.
So to do that, we can use the micro mapper right inside this mapper. So like we saw
a second ago, we'll add a public function underscore, underscore construct. The
private micro mapper interface, micro mapper. And down here, I will do a little bit
of fancy code, I'll say DTO arrow, Dragon Treasures equals. Then I'm going to do
an array map, which is going to give you the Dragon Treasure argument. I'll finish
that method in a second, but let me pass the array here. So entity arrow, get
published Dragon Treasures, arrow to array. So if you don't use array map very often,
what's going to happen here is we're going to get all of the published Dragon
Treasures. We're going to take this array, and then PHP is going to loop over that
and call our function every time passing us each Dragon Treasure in this array.
Whatever we return from this is going to become an item inside this Dragon Treasures.
So what we want to return from this is a Dragon Treasure API object. So to do that,
we can say return this arrow micro mapper, arrow map, map that Dragon Treasure over
to a Dragon Treasure API colon colon class. So it's a fancy way of taking each of
these Dragon Treasure entity objects, running it through the micro mapper. And then
we ultimately should get an array of Dragon Treasure API objects right there. Cool.
Easy enough. I like that. And when we refresh, we are greeted with a different
circular reference problem. This one's actually coming from micro mapper. And this
is a problem that's going to happen when you have relationships that refer to each
other. So think about it, it makes sense. We ask the micro mapper to convert our
Dragon Treasure entity to a Dragon Treasure API. Cool. To do that, it uses our mapper
class. And guess what? In our mapper class, we ask it to convert the owner, the user
to a user API instance. To do that, it's going to take to go from the user entity
to the user API, it's going to go back to user entity API and go to this mapper again.
So we've got our same kind of like circular loop here happening from user to Dragon
Treasure back to user. So there's a nice way to fix this, but also the fix is to
go into your mapper. And when you call that map function, you can pass a third
argument, this is context, it's kind of options. And built into micro mapper, there's
just one option, it's micro mapper interface colon max depth equal to one. And I'll
show you what that does. When we refresh, we see our dump here, or that dump is coming
from our state provider. So after our state provider maps all the entities to the
user APIs, this is what we get back five API, five user APIs. And you can see the
Dragon Treasure property is populated with a Dragon Treasure API. So did go and do
the mapping from Dragon Treasure to Dragon Treasure API. But then when it went to
map the user entity to user API, this user API, notice it's empty, it's a shallow
mapping. So by saying depth one, instead of here, we basically say, hey, I want you
to fully map this Dragon Treasure entity to this Dragon Treasure API. But if there's
any further mapping that needs to be done, I want you to skip that. And it's not
that it really skips it, what happens is when it starts when it gets into this mapping
and sees that micro mapper is called a second time, when it does this mapping from
the user entity to the user API, internally, it calls the load method, but it never
calls the populate method. So you end up with a user API object with the ID, but
nothing else. So this is kind of the way that micro mapper allows you to avoid these
circular reference problems. So again, to say in a different way with depth one,
it means our Dragon Treasure gets fully mapped, but anything that it needs to map
kind of gets this shallow mapping with just its ID. All right, let's check out the
DD so we can see the results. And perfect. The result is exactly what we expected.
Now if you think about it, you don't have to, but we saw a second ago that internally
these Dragon Treasure DTO objects, API objects, in our API. All we're really showing
for Dragon Treasures is just the IRI. So if you wanted to, it's, I should say that
basically as a rule, you should always set max depth to one. Now if you want to,
when you're mapping, sorry, I'm jumping around here. We could set this max depth
to zero, only reason to do this would be just slight performance. Let me put the
DD back so you can see what the difference is here. So this case, when we're mapping
the Dragon Treasure to Dragon Treasure API, we're doing max depth equals zero. The
result of that is it means it hits its limit immediately. So when it goes to map
the Dragon Treasure entity to the Dragon Treasure API, it uses our mapper, but it
only calls the load method, it never calls populate at all. So we end up with a
shallow object for our Dragon Treasure API. Now that might seem weird, but it's
technically okay because the only thing we need, this Dragon Treasures array is going
to be rendered as IRI strings, and the only thing that's needed to do that is the
ID. So check this out. If I take the dump off of there, it looks exactly the same.
We just saved ourselves a little bit of trouble on how deep we mapped. So use max
depth of one just to be on the safe side, but if you know that you're only using
IRIs, then you can set max depth to zero if you want to. So as I mentioned, every
time you use the micro mapper inside of a mapper, as a best practice, you probably
want to set a max depth of at least one. So I'm going to set the same thing over
here, micro mapper interface, max depth, and I'll zero because I know that we're
just showing the IRI in that case as well. Now one other thing I did just notice
is notice our Dragon Treasures is suddenly like an object with a one key and a two
key and a zero, one, three. This used to be an array. That is actually coming from
array map. Array map is returning an array with like, you know, the zero key set
to something and the two key to set to something. So because of that, when it's
serialized, it looks like an associative array. So we're getting the curly braces
around here. And perfect. We're back to just our actual array of items. We don't
really care about the keys inside of there. All right, next. We can read from our
new Dragon Treasure resource, but we can't write to it yet. Let's create a Dragon
Treasure API to entity mapper and read things like security and validation.
