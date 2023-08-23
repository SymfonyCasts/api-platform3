# User Dto

Coming soon...

The fastest way to get started with API Platform is by adding these API resource
attributes above your entity classes. That's because API Platform gives you that free
state provider that queries from the database, includes pagination and filters, and
of course it also gives you a free state processor which saves things to the
database. But as we've seen with DailyQuest, that's not actually required. And if
your API starts to look quite a bit different than your entities, like different
properties on your entities versus different fields in your API, then it might make
sense to separate your entity class from and have a separate API resource class. So
far we have not done this. We've had our entities are our API resource classes and it
has added some complexity. For example, we have a custom IsMinedField which is
actually powered by this IsOwnedByAuthenticatedUser property, which is a
non-persisted property that we actually populate via a state provider. And probably
the most noticeable thing are the serialization groups. So we have to use
serialization groups like treasure colon read so that we can include the properties
we want but avoid the properties that we don't want. So this has saved us time but
we've had to kind of use some tricks to bend our entity to look how we want it to in
the API. So maybe a better way, especially if you're building a really big custom
API, though a bit slower at first, is to use a dedicated class for your API from the
start. That's often called a DTO, a data transfer object. I'm going to use that term
a lot. I'm just referring to a class that is an API resource. So we're going to start
by replacing the user, all the API stuff, we're going to move that from the user
entity to its own dedicated class. So I'm actually going to start by deleting a whole
bunch of stuff here. So we have this API resource, we have another sub resource API
resource, I'm going to get rid of the filter. I'm going to get rid of the validation.
Now you might maybe have, maybe you have some forms in your system where you still
need the validation, but in our case we're not going to need to get validation. Make
it rid of all the things up there related to validation and serialization. And let's
see, we're just going to kind of go little by little. I want to get this whole thing
cleaned out. No more serialization API platform or validation stuff inside of here.
Which is going to make this class actually quite a bit smaller. And let's see, is
that everything? Looks like it. New statements on top look good. Awesome. So maybe I
forgot, should have gotten most of the API things removed. And I'm also going to
remove the state processor we have for this. This is responsible for hashing the
plain password and setting it onto the password property. We are going to
re-implement that and a lot of the stuff we just deleted later. I want to get a clean
slate to start with. So if we look at the docs right now, we're now reduced to quest
and treasure. That's it. All right. So we're going to start just like we did with the
daily quest in the API resource directory. We're going to create a new class. I'm
going to call it user API to indicate this is the user class for my API. And we're
going to add API resource to it. All right. So far, that's just like any other custom
API resource. It shows up here. If we try the get collection operation, like with our
daily quest, it's a 404. So basically, it shows up in our docs. We can use the
endpoints, but nothing actually works yet. And like before, we can see that we're
missing the ID part on some of these operations. So to fix that, our users are going
to have an int ID. So I'm going to say public. Int ID equals null. And again, I'm
using public properties here, because it just makes life easier for these classes
that have this very simple use case of representing our API. So as soon as we do
that, API platform recognizes that that's the identifier, and our operations are
looking good. And while we're here, let's also modify the short name. This is called
user API, not a super great name for an API. So let's say short name, user. And
suddenly, this starts to look a lot like what we had before. All right, so like with
our daily quest, to get this to work, we're going to need a state provider and a
state processor. Let's add the state provider next, but with a twist that is
leverages a brand new API platform feature that's going to save us a lot of work.
