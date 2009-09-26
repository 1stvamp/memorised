from memorised.decorators import memorise
import unittest
import memcache
import random

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
	return random.uniform(0, 100)

def func_get_b():
	return random.uniform(0, 100)

class TestMemorise(unittest.TestCase):
	def setUp(self):
		self.mc = memcache.Client(['localhost:11211'], debug=0)

	def testsimplefunction(self):
		pass

	def testinstancemethod(self):
		pass

	def testclassmethod(self):
		pass

def run():
	unittest.main()

if __name__ == '__main__':
        run()
