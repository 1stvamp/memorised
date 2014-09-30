memorised
=========
.. image:: https://travis-ci.org/1stvamp/memorised.svg?branch=master
    :target: https://travis-ci.org/1stvamp/memorised


About
-----

``memorised`` is a python module containing handy ``python-memcached``
decorators and utils.
Specifically the ``memorise`` decorator allows you to quickly and simply
add memcache caching to any function or method.

Installation
------------

Install ``memorised`` using pip::

    pip install memorised

Or using the supplied ``setup.py``::

    python setup.py install

Usage
-----

To cache a simple unbound function, just include the ``@memorise()`` tag to the
function definition (the paranthesis are needed as the decorator needs to be
initialised at the time of binding to handle ``memorise`` specific arguements)::

    from memorised.decorators import memorise

    @memorise()
    def myfunction():
        return 'hello world'

You can do the same for simple instance and class methods, however for most
instance methods, e.g. when caching results for database models, you probably
want to include some form of identity to single out a method call on one
instance from another instance. You can do this by providing a list of one or
more `parent keys`, these are the names of attributes in the parent instance
that you want to be appended to the memcache key::

    class MyModel:
        id = 1

        @memorise(parent_keys=['id'])
        def get_stats():
            return blah()

For other usage examples see the unittests in ``tests.py``.

