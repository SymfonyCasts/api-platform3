# Serialization Groups

Right now whether a field is readable in our API or writeable in our API is entirely
deterred by whether or not it's accessible in our class. So basically whether or not
it has a getter method or a setter method. But what if you need a getter or setter
but you don't want that field exposed in the api? Well, you have two options. Option
number one is to create a

A DTO class for your API resource. This is something we're going to talk about in a
future tutorial. It's where you actually create a dedicated class for your, for your
Dragon treasure API that probably has a lot of the same fields as this. And then you
move the API resource onto that. It creates a little bit more work setting things up,
but the advantage is that you have a dedicated class for your api. So you just
literally always make, make your class look exactly like you want it to look in the
API and you don't need to worry about and then, and you're done. The second solution
and the one we are going to use is serialization groups. So check this out. Hop on
our API resource. I'm going to add a new option here called normalization context.
Now remember, normalization is the process of going from an object into an array. So
this is like when you are uh, making a get request to read a treasure. The
normalization context is basically options that are passed during that process. And
the one option that's most important by far is groups. So I'm going to pass a new
group here called Treasure:Reid. We're going to talk about what this does in in a
minute, but you can see the pattern for the name I'm using here is just basically the
name of my class going to be Dragon Treasure if I wanted, and then:Reid, because this
is the normalization means that we're reading this class.

So what does that do? Let's find out. I'm going to refresh the documentation and
actually it's make life easier. Let's actually just go to the URL directly. Oh, it's
not called Dragon Treasures anymore. It's called treasures. There we go. And
absolutely nothing is returned anymore. So check this out, Hydra. Remember we, this
is our array of resources. So it is returning one treasure, but then other than these
weird the at ID and at type fields, there's no actual fields being returned from our
resource. So here's how this works. Now that we have this normalization context on
here, what that means is when our object is normalized, it's only going to inclu
include properties that have this group on it. And since we haven't added groups to
any of our properties, it returns nothing. How do we add groups? That's with another
attribute. So above the Indian field, I'll do groups. I'll hit tab to add a use
statement and they'll say Treasure coin read. And then let's do this above the
description field. Do we want that to be readable? The value field and cool factor,
we'll start just with those. Now I'm going to go refresh your endpoint. Got it. Name,
description, value, cool factor. So we have control over which fields are returned.
So we can do the same thing for which fields are writeable. So that is called DN
normalization. So I bet you can guess what we're going to do here. Copy that

Paste, call it D normalization context and we'll call it treasure colon, right? And
then immediately we will go below down here and let's add treasurer qu right to the
name field. I'm going to skip the description one for now. Remember we actually
deleted our set description method earlier. We'll add it to the value field and the
cool factor field. And you can see it's mad at me because I forgot. As soon as you
pass multiple groups in here, we need to make this an array. So I'll add an array
around those three properties. There we go. So now this property is in these two
groups to see if this is working. I'll go and refresh the documentation and open up
the put endpoint and it shows us, yep, name, value and cool factor are now the only
fields that are settable on our api. Now we are missing a couple of things because if
you remember last time we made a get plundered at a go method and we want this to be
included when we read our resource. Right now if we we check our endpoint, it is not
being included. So we can also add the same group above methods. So groups

And I'll do treasure:read and now it pops up. And then let's find the set text
description method. We'll do the same thing there. Groups, treasure column, right?
Awesome. And if we go to the documentation, you can see that was not there a second
ago. Now if we refresh and check out the put endpoint s text description is back. And
this means if we want to, we can put back some of the setter methods that we removed
a second ago. So maybe I do need a set description method like in my code to be able
to do things. So I'm going to copy set name to be lazy and rename description name,
uh, rename name to description, a couple places. Got it. And of course the nice thing
is even though I have that setter back now when I look at my put endpoint, it doesn't
automatically show up cuz we've taken control with thanks to our DN normalization
fields. I'm also going to add that same thing for plundered at sometimes it's handy
like in your data fixtures especially to be able to actually set the plundered at. So
I will quickly add that as well. Probably should have been lazier and generated the
setter, but now we're done.

Okay, so we know that fetching and API fetching a resource works. Let's now see if we
can create a new resource. So by the post endpoint, I'll hit try it out. And let's
fill in our new treasure, which is of course our giant jar of pickles. This is very
valuable. Cool factor of 10. And I'll add a description.

All right. And when we try this, oh 500 air an exception occurred. Not no violation
column is published, violates the not no constraint. So we have made our API down to
just the fields that we want to be writeable, but we're still missing one field that
needs to be set in the database. If you scroll up and find that is published, okay,
it = null. Let's change that to = false by default, then we actually don't need that
bull. The property's not null of anymore. And now we try it. Giant jar pickles is
loaded into the database. It works. All right, next I want to show a few more cool
serialization tricks that'll make your class awesome and your api. Awesome.
