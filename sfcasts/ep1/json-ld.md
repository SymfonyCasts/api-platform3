# JSON-LD: Giving Meaning to your Data

Okay, apparently I've just used the get end point to get all of my resources. I have
a treasure with the ID of one. So let me close up this resource. I'll use this other
get end point hit try it out, put one in for the ID and we'll fetch what just one
looks like and beautiful. But I have a question. What are the significance of these
fields? Like what does name or description or value actually mean? Are they, is the
description plain text? Is it html? Is this name like a short name given to the item
or is it a proper name? Is this value in dollars? Euros? What the heck does cool
factor mean? <laugh>. So if you're a human, you are right, then you can probably
figure out a lot of that information. But com machines, okay, maybe minus futuristic
ai, they can't figure this out. They don't know what these keys actually mean. They
don't have. The question is how can we give context and meaning to our data? So
there's this thing called R D F Resource Description framework, which is kind of a
set of rules about how we can describe the meaning of data so that computers can
understand, understand it. It's a boring and abstract, but it's basically a

Guide on how you can say that one piece of data has this type or one resource as a
subclass of some other type. In html, you can a attributes to your elements to add
this R D F metadata. You can say that this dib describes a person and that this
person's name and telephone are these other pieces of data. This makes your random
HTML in your site actually understandable by humans. It's even better if two
different sites use the exact same definition of person, which is why the types are
URL insights. Try to reuse existing types rather than invent new ones. So J S L D
does the same. So as you can see, this is really returning J S O, but you can see in
the content type that it says it's application /LD plus JS O. What that simply means
when you see app, when you see application /LD plus J S O is it's saying that what
we're going to get back is J S O N, but it's going to contain a couple of extra
fields that add more data according to the JS O LD rules. So JSONs LD is just JS O
with extra info.

So for example, every resource like our Dragon Treasure has three at fields. Most
important is probably at id. This is very simply the unique identifier to this
resource. It's basically the same as id, but it's even better because it's a url. So
instead of just saying like ID one, you have ID eight /API /dragon treasures /one. So
not only is that going to be unique when compared to like other resources, like we
have users in the future, it's also a url. You can actually like go to this in your
browser and if you have the accept header or if you add.JSON L, the end at the end of
it, whoops, let me get rid of my extra /yeah, you can go get that resource right
there. So add ID is just that simple and it's awesome. The other thing is ADD type.
That's a way to describe the type of this resource, like what fields it has. And if
we have two different resour, if we have two different, if we get back two different
resources and they both have ad type Dragon Treasure, we'll know that they're the
same.

This ad type is almost like a class definition that describes which fields are
allowed, how can we actually see where this type is defined? That's where a context
can help us. This is a URL to where we can get more information about the meaning of
this. So I'm going to copy that url, pop that in your browser and beautiful. We get
this very simple document here that basically says Dragon Treasures have name,
description, value, cool factor created at in is published properties. And if we want
to get even more information about what those actual individual properties mean, we
can follow this at vocab link here to a big documentation, big other piece, pieces of
documentation that talks about, for example, all of our, all of the classes that we
have, like Dragon Treasure. And then you can see all the different properties like
there's a name property. You can see it's a required false readable, true writeable
true and also that it's a string. And you see this for all of our properties down
here for value. You can see that the string is, this is actually an integer and this
XMLs:integer is actually referring to another document which is up here on top, which
actually gives you a link to where you can get information about what that means. So

Yes, it, so at this point you might be thinking, this seems a lot like the AP Open
API documentation and you're right, but more on that in a second. And yes, this is
like a little bit confusing, but imagine what this would look like to a machine. It
would be informational gold. You could figure out what all of the fields are and what
the meanings of those fields are. And as you can see down here under value Hydroco
description, it picked up the PHP documentation that we added to that field a second
ago. Just like the open API doc, we can also add some extra information above our
class. We could actually add this via pH documentation like normal, but this API
resource actually also has some options. We can pass to it if we want. For example,
one of those is called a description. We could say a rare and valuable treasure. And
all this is doing is, is giving information to help build. If I refresh the JSON LD
page and search for Rare, this actually fills out the Hyd description for if I close
a couple things here. There we go. High description actually for the Dragon treasure
itself. So it provides more metadata.

And not surprisingly, this also shows up over here inside Swagger because that was
also added to our open API specification. Next we need to talk about one last little
uh, uh, theory thing, and that is what this Hydra thing means. That's showing up
outta the place. Also explain the difference between JSON LD and Open API and why we
have both of them in our app.
