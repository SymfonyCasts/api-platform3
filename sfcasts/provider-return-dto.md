# Provider Return Dto

Coming soon...

But let's keep track of our goal here. When we originally used the state options, it
triggered the core doctrine collection provider to be used. That's great, except that
it returned the user entities. And so the user entity objects became the central
objects internally whenever we were using our user API endpoints. That caused the
serious limitation when serializing. Our user API properties needed to match our user
properties because basically the serializer was taking those user objects and kind of
serializing them into our user API objects. To fix that and give us full control, our
solution is to create our own state provider, call the collection provider like we
are, but then ultimately return user API objects from here instead of user entity
objects. Then our user API objects will become the central objects when using the
user API endpoints and they'll serialize totally normally. No weirdness where it
tries to convert a user into a user API. That'll free us up to have whatever
properties we want on user API. So our job now is pretty simple. We need to loop over
all of these user entity objects and convert them to user API objects. This is kind
of beautifully boring. I'm going to create a DTOs array, then we're going to foreach
over entities as entity. Then I'm going to add that DTOs array by calling a new map
entity to DTO method. I'll hit alt enter to add that method down here. We know this
is not going to be mixed. This is going to be an object. In this case we actually
know it's going to be user object, but I'm trying to keep this class somewhat
generic. This will return a different object, a user API object. Then I'm just going
to paste in some logic here. Then let me hit alt enter and import that class to add
that use statement. So very simple. We're going to be taking in the entity, which we
know is going to be a user entity, and we're just going to put the data onto the DTO
and return it. The only thing that's kind of fancy is I'm changing this collection to
an array because our user API is using an array on its property. That's it. Then down
here at the bottom we're going to return DTOs. Yeah. Now internally it's going to
serialize these DTOs instead of these entities, and it works just like before. The
big difference is that our user API is now the central object, and so it's being
serialized like normal, and that means we are free to have custom properties. So I'm
going to put our public int flame throwing distance back, and then in our provider
this is where we have the opportunity to set those custom properties. Like DTO arrow
flame throwing distance equals rand between one and 10, and voila. We have the
ability to add custom properties. We're reusing the core doctrine collection
provider, but with the ability to add custom fields. Oh, and I forgot to mention, now
that we've done this, our JSON-LD fields at ID and at type are now properly back. So
this is it. This is the promised land here. Though, we did miss one thing. We're
missing pagination. You can see our filter stuff is documented down here, but
normally we have a little spot down here that also explains the pagination. Now in
reality, it is paginating. Watch. If I go to question mark page equals two, you can
see this is user one right now, and it becomes user six. So internally, the core
collection provider from doctrine, it's still reading the current page, and it's
still querying for the correct set of objects for that page. But those stuff at the
bottom, the Hydra stuff at the bottom that describes the pagination is missing, which
advertises the pagination. And that's because we are no longer returning an object
that implements pagination interface. Remember, I'm calling this entities, but this
is actually a pagination object. Now that we're returning just an array, it kind of
makes API platform think that we don't support pagination when we do. So the solution
is really simple to this. We're going to take our DTO, our array of DTOs, and just
put those into a pagination object. So watch. Instead of returning DTOs, we'll return
new traversable paginator like we did before. And we'll use the same array iterator
to pass that array in. Then here we just need to pass a couple of things like the
current page, items from page, and total items. And we can actually get all of that
from the paginator object up here. Remember, this is not an entities. We dumped that
in the last video. This is actually an instance of paginator. So I'm going to say
assert entities instance of paginator, the one from Doctrine ORM, to help on my
editor. And then down here, it's pretty easy. Entities arrow get current page,
entities arrow get items per page, and entities arrow get total items, because it
already did all of that work for us. How nice is that? Now when we refresh, the
results, of course, don't change. That never was. But the key thing is down here, the
Hydra view. Our pagination is back. All right, next. Let's get this working for our
item operations, like get one or patch, and leverage our new system to re-add
something custom to user API that we previously had, except now we're going to do it
in a cooler, simpler way.
