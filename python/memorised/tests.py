from memorised.decorators import memorise
from memorised.utils import uncache
import unittest
import memcache
import uuid

def unique():
	return str(uuid.uuid4())

class TestModel:
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

@memorise()
def func_get_a():
        return unique()

def func_get_b():
        return unique()

class TestMemorise(unittest.TestCase):
        def setUp(self):
                self.mc = memcache.Client(['localhost:11211'], debug=0)

        def testsimplefunction(self):
		uncache(func_get_a)()
                a1 = func_get_a()
                a2 = func_get_a()
		self.assertEqual(a1, a2)
		b1 = func_get_b()
		b2 = func_get_b()
		self.assertNotEqual(b1, b2)
                pass

        def testinstancemethod(self):
		t = TestModel()
		u = unique()
		t.set_a(u)
		uncache(t.get_a)(t)
		self.assertEqual(u, t.get_a())
		t1 = TestModel()
		self.assertEqual(u, t1.get_a())
                pass

        def testclassmethod(self):
                pass

def run():
	print
	print "Running memorised.decorators test suite..."
	print
        suite =	unittest.TestLoader().loadTestsFromTestCase(TestMemorise)
        unittest.TextTestRunner(verbosity=2).run(suite)

if __name__ == '__main__':
        run()
