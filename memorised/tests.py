# -*- coding: utf-8 -*-

from __future__ import unicode_literals
import unittest
import uuid

import memcache

from memorised.decorators import memorise, memcache_none


class instrumented_memorise(memorise):
        """
        A version of the memorise decorator that records the number of cache
        and function calls it makes.
        """

        def __init__(self, *args, **kwargs):
                super(instrumented_memorise, self).__init__(*args, **kwargs)
                self.reset()

        def __call__(self, *args, **kwargs):
                func = super(instrumented_memorise, self).__call__(*args, **kwargs)
                func.mem = self
                return func

        def reset(self):
                self.get_calls = 0
                self.set_calls = 0
                self.function_calls = 0

        def call_function(self, *args, **kwargs):
                value = super(instrumented_memorise, self).call_function(*args, **kwargs)
                self.function_calls += 1
                return value

        def get_cache(self, *args, **kwargs):
                value = super(instrumented_memorise, self).get_cache(*args, **kwargs)
                self.get_calls += 1
                return value

        def set_cache(self, *args, **kwargs):
                value = super(instrumented_memorise, self).set_cache(*args, **kwargs)
                self.set_calls += 1
                return value


def unique():
        return str(uuid.uuid4())


class Model:
        c = None
        d = None

        def __init__(self):
                self.a = None
                self.b = None
                self.id = 1

        def set_a(self, a):
                self.a = a

        @memorise(parent_keys=['id'])
        def get_a(self):
                return self.a

        def set_b(self, b):
                self.b = b

        def get_b(self):
                return self.b

        @classmethod
        def set_c(cls, c):
                cls.c = c

        @classmethod
        @memorise()
        def get_c(cls):
                return cls.c

        @classmethod
        def set_d(cls, d):
                cls.d = d

        @classmethod
        def get_d(cls):
                return cls.d


@memorise()
def func_get_a():
        return unique()


def func_get_b():
        return unique()


@memorise()
def func_get_c(foo=None, bar=None):
        return unique()


@memorise()
def func_get_d(bar=None, foo=None):
        return unique()


@memorise()
def func_unicode():
        return '§'


@instrumented_memorise()
def func_none():
        return None


@instrumented_memorise()
def func_zero():
        return 0


@instrumented_memorise()
def func_nonzero():
        return 1


class TestMemorise(unittest.TestCase):
        def setUp(self):
                self.mc = memcache.Client(['localhost:11211'], debug=0)
                self.mc.flush_all()

        def testsimplefunction(self):
                a1 = func_get_a()
                a2 = func_get_a()
                self.assertEqual(a1, a2)
                b1 = func_get_b()
                b2 = func_get_b()
                self.assertNotEqual(b1, b2)

        def testinstancemethod(self):
                t = Model()
                u = unique()
                t.set_a(u)
                self.assertEqual(u, t.get_a())
                t1 = Model()
                self.assertEqual(u, t1.get_a())
                t.set_b(u)
                self.assertEqual(u, t.get_b())
                t.set_b(1)
                self.assertEqual(1, t.get_b())

        def testclassmethod(self):
                u = unique()
                Model.set_c(u)
                self.assertEqual(u, Model.get_c())
                Model.set_c(1)
                self.assertEqual(u, Model.get_c())
                Model.set_d(u)
                self.assertEqual(u, Model.get_d())
                Model.set_d(1)
                self.assertNotEqual(u, Model.get_d())

        def testkwargs(self):
                c1 = func_get_c()
                c2 = func_get_c()
                self.assertEqual(c1, c2)
                c3 = func_get_c(foo=1)
                self.assertNotEqual(c1, c3)
                c3 = func_get_c(1, 2)
                c4 = func_get_c(foo=1, bar=2)
                c5 = func_get_c(bar=2, foo=1)
                self.assertEqual(c3, c4)
                self.assertEqual(c3, c5)
                d1 = func_get_d(1, 2)
                d2 = func_get_d(foo=2, bar=1)
                d3 = func_get_d(bar=1, foo=2)
                self.assertEqual(d1, d2)
                self.assertEqual(d1, d3)

        def test_return_value_unicode(self):
                f = func_unicode
                value = '\u00A7'  # § (section) symbol.
                a1 = f()
                self.assertEqual(a1, value)
                a2 = f()
                self.assertEqual(a1, a2)

        def test_return_value_none(self):
                f = func_none
                f.mem.reset()
                self.assertEqual(f.mem.function_calls, 0)
                a1 = f()
                self.assertEqual(f.mem.function_calls, 1)
                # The function should give us back None, and the cache should
                # contain the memcache_none sentinal value.
                self.assertEqual(a1, None)
                self.assertTrue(isinstance(f.mem.get_cache(f.mem.key(f, (), {})), memcache_none))
                a2 = f()
                self.assertEqual(f.mem.function_calls, 1)
                self.assertEqual(a1, a2)

        def test_return_value_zero(self):
                f = func_zero
                f.mem.reset()
                self.assertEqual(f.mem.function_calls, 0)
                a1 = f()
                self.assertEqual(f.mem.function_calls, 1)
                self.assertEqual(a1, 0)
                self.assertEqual(f.mem.get_cache(f.mem.key(f, (), {})), 0)
                a2 = f()
                self.assertEqual(f.mem.function_calls, 1)
                self.assertEqual(a1, a2)

        def test_return_value_nonzero(self):
                f = func_nonzero
                f.mem.reset()
                self.assertEqual(f.mem.function_calls, 0)
                a1 = f()
                self.assertEqual(f.mem.function_calls, 1)
                self.assertEqual(a1, 1)
                self.assertEqual(f.mem.get_cache(f.mem.key(f, (), {})), 1)
                a2 = f()
                self.assertEqual(f.mem.function_calls, 1)
                self.assertEqual(a1, a2)


def run():
        print()
        print("Running memorised.decorators test suite...")
        print()
        suite = unittest.TestLoader().loadTestsFromTestCase(TestMemorise)
        unittest.TextTestRunner(verbosity=2).run(suite)


if __name__ == '__main__':
        run()
