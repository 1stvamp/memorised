from memorised.decorators import memorise

class Test:
        def __init__(self):
                self.t = None

        
        @memorise(set_key='t')
        def set_t(self, t):
                self.t = t
                return True

        @memorise()
        def get_t(self):
                return self.t

def run():
	test = Test()
	print test.set_t(t='test')
	print test.get_t()

	test = Test()
	print test.get_t()
