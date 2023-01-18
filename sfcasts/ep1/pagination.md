# Pagination

Coming soon...

We're gonna start doing more with our data. So let's add some rich fixtures to our
site. For fixtures, I like to use foundry, so I'm gonna compose the choir foundry and
then also the actual doctrine fixtures library. So ORM fixtures and install both of
those as dev dependencies. Perfect. Once that finishes, let's run Bin console. Make
factory, if you haven't used foundry before, the idea is that you have a factory
class for each entity that helps you create those really quickly. So I'll hit zero to
generate the one for our one dragon treasure entity. So end result is that we now
have a source factory Dragon Treasury factory object. This is an object that's just
really good at creating a dummy dragon treasures and then get defaults. It even has a
bunch of nice random data kind of pre-filled in. I'm actually going to paste over
that with some code that I've updated. I modified a little bit and the only thing we
need here is for name I was choosing from a new treasure's name constant. And I'm
also going to paste that in which you can grab from this page. Just give us something
kind of interesting to work with. All right, so this class is done. Step two, to
actually load this as data fixtures and app fixtures. I'll clear out the load method
and all we need is Dragon Treasure factory, colon, colon create many, and let's
create 40 dragon treasures. All right, let's load this thing back at the Terminal Run
Symphony Console Doctrine, fixtures Load. Say yes and cool. Let's see if it works

Back at our api. I'll refresh. Let's try the get collection endpoint. Hit execute and
beautiful. Look at all those awesome eight treasures. So remember we added 40, but if
you look here, even though the IDs didn't stay, sorry, one, there's definitely not 40
here. It says Total items 40, but it's only showing 25 of them. And down here there's
this Hydra view, which kind of explains why there's Builtin page nations. So right
now we're looking at page one and we can also go to see the last page is and what the
next page is. So yes, we're talking about cult AP as a return. A collection of items
endpoints though is need Page Nation with API platform, you just get that for free.
So an easy way to look at this is let's go to slash api slash treasures that Jason
ld. So here is page one and then we can add question Mark Page equals two. And now we
get page two. That is just so awesome to not have to worry about. Now if you need to,
you can change some Page Nation options. So for a spec to your terminal run bin
console, debug config API platform. So in general, there's a lot of things that you
can configure on API platform. And this command's gonna show us the current
configuration for API platform. So for example, you can actually add a title and
description to your api. This becomes part of the open API spec and it shows up on
your documentation. And there is a lot of other things that you can do inside of
Fear.

If you search for Page Nation, we don't want the one under Graph ql. Here we go. We
want the one under Collection. Collection Page Nation. You can modify some things
here. One of the, now again, this is showing you the current configuration so it
doesn't show you all of the possible keys here. So there's actually a, a key here
called Page Nation Items per page that you could set to globally change the default i
a number of items per page. So as a reminder, we just ran debug config. The other
command that you wanna always remember is config, dump config, debug config shows you
the current configuration config dump shows you a full tree of possible
configuration. So there it is, page Nation Max items per page under, actually it's
under a defaults key. This is actually pretty cool cause this is showing you a bunch
of defaults that you can specify. These are actually the same options that you'll
find in your a API resource attribute. So you can specify the Page Nation maximum
items per page globally, or you can take that same option and go into Dragon
Treasure, find your API resource attribute

And add page nation items per page here. And let's try this. We'll set this to 10. So
again, you can see here the keys that we're seeing here are also the, are the same
keys that you see inside of this under this default configuration. So just nicely to
be aware of that you can give all of your API resource attributes some options by
default. Before we try this over here, let's go back to page one and beautiful, much
shorter list. And now you can see that we have four pages instead of just two. There
are also other things you can configure. For example, you can allow your API client
to say how many they want per page. So you could let them choose. Check out the
documentation for how to do that. Alright, now that we have a lot of data, let's have
the ability for our Dragon API users to search and filter through the treasures. Like
maybe a dragon is looking for a hoard of individually wrapped candies. Hmm, that's
next.

