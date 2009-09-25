from memorised.decorators import memorise

class Test:
        def __init__(self):
                self.t = None
                self.id = 1


        @memorise(set_key='t', parent_keys=['id'])
        def set_t(self, t):
                self.t = t
                return True

        @memorise(parent_keys=['id'])
        def get_t(self):
                return self.t

def run():
        test = Test()
        print test.set_t(t='test')
        print test.get_t()

        test = Test()
        print test.get_t()


if __name__ == '__main__':
        run()