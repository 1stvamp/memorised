from memorised.decorators import memorise

class Test:
        def __init__(self):
                self.t = None
                self.id = 1


        def set_t(self, t):
                self.t = t
                return True

        @memorise(parent_keys=['id'])
        def get_t(self):
                return self.t

def run():
        #test = Test()
	#print "set_t(t='test'): %s" % test.set_t(t='test')
	#print "get_t(): %s" % test.get_t()

        test = Test()
	print "get_t(): %s" % test.get_t()


if __name__ == '__main__':
        run()
