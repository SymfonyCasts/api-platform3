# Relations Iri

Coming soon...

When we tried to create this dragon treasure for the owner, we put the ID of an owner
in the database and we found out the, A platform did not like that. It said expected.
I r I. What is an iri? Well, if you go back down to the GI users collection endpoint,
we know that every resource that comes from an API has an at ID on it set to the URL
to where you can fetch that resource. This is the I R I I I stands for International
Resource Identifier, and it's meant to be kind of like a unique identifier across
your entire api. The number one is not a unique identifier, but this whole U URL is a
unique identifier and as I've said, a U URL is all a heck of a lot more handy than
just an integer id anyways, so when we want to set a relation property, we need to
use the I R I slash API slash users slash one. When we hit execute, it works 2 0 1
status code, and when the owner comes back to us, it once again uses the iri. So the
takeaway is that relations are just normal properties,

But we get and set them via their I R I string, and I just think this is such a
beautiful and clean way to handle this. All right, let's talk about the other side of
the relationship. I'm actually gonna refresh the whole page here and let's go to our
get one user endpoint and let's fetch that user with ID one. And there's the basic
data. So the question I have now is, could we add a treasure's field here that shows
all the treasures that this user owns? Well, think about it. We know how the
serializer works by just serializing the properties on user. And we do have a Dragon
Treasures property on user. So let's expose this to our api. I'll add groups with
just user calling read for now. Later we'll talk about what, how you can write to a
collection field, but for now, just make it readable.

All right. When we refresh and go look at the same get end point down here, yeah, you
can see Dragon Treasures is showing up in the example config. So let's try this. I'll
use ID one again. It executes and ugh, gorgeous. What it gives us is an array of I R
I strings. I love that. These are so powerful because if we need more information
about these, we can make a request to these end points to get all of those treasure
details. And if you get really fancy and use something like Vulcan, you could even
preload those so that the server pushes to them. But I do kind of though, as cool as
that is there is kind of one obvious question here, which is, what if I, what if
needing the Dragon treasure data for a user is so common that to avoid the extra
requests, we just want to embed the data right here, like objects of data, Jason,
objects of data instead of these strings. Can we do that? Absolutely. Let's find,
let's find out how next.

