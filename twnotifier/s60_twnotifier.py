# -*-python-*-

__package__    = "s60_twnotifier.py"
__version__    = "1.0"
__author__     = "Aaron Straup Cope"
__url__        = "http://www.aaronland.info/python/twnotifier/"
__cvsversion__ = "$Revision: 1.2 $"
__date__       = "$Date: 2006/11/25 16:59:56 $"
__copyright__  = "Copyright (c) 2006 Aaron Straup Cope. Perl Artistic License."

from twnotifier import * 

import e32
import appuifw

class s60_twnotifier (twnotifier) :

    def __init__ (self, storepath) :

        twnotifier.__init__(self, storepath)

        self.__lock__  = e32.Ao_lock()
        self.__timer__ = None
        self.__tw__    = []

        appuifw.app.exit_key_handler = self.abort

    #
    #
    #

    def abort(self) :

        if self.__timer__ :
            self.__timer__.cancel()
            
        self.__lock__.signal()

    #
    #
    #
    
    def loop (self) :

        if self.setup() :
            self.run()
            
        self.__lock__.wait()
        
    #
    #
    #
    
    def setup_notification_agent(self) :

        self.log("Setting up notification agent")

        self.__vibrate__ = False
        self.__miso__    = False
        self.__misty__   = False
        
        try :
            import miso
            self.__vibrate__ = True
            self.__miso__    = True
            return True
        except :
            pass

        try :
            import misty
            self.__vibrate__ = True
            self.__misty__   = True
            return True
        except :
            pass
        
        return True
    
    #
    #
    #

    def pre_notify (self) :
        self.clear_log()
        
        self.__timer__ = None
        self.__tw__    = []
        
    #
    #
    #
    
    def post_notify (self) :
        self.log(u"\n\n".join(self.__tw__))        
        self.vibrate()

    #
    #
    #
    
    def vibrate (self) :
        if not self.__vibrate__ :
            return True

        try :
            if self.__miso__ :
                miso.vibrate(500, 100)
            elif self.__misty__ :
                misty.vibrate(500, 100)
            else :
                pass
        except :
            self.__vibrate__ = False
            
    #
    #
    #
    
    def notify (self, tw) :
        msg = u"%s\n(%s, %s)" % (tw['what'], tw['who'], tw['when'])        
        self.__tw__.insert(0, msg)        
        return True
            
    #
    #
    #

    def prompt (self, query, type) :
        return appuifw.query(unicode(query), "text")

    #
    #
    #
    
    def log (self, msg, error=0) :
        self.__msg__ += "%s\n" % msg
        self.write(unicode(self.__msg__))

    #
    #
    #
    
    def write(self, txt) :
        
        if e32.in_emulator() :
            t = appuifw.Text()
            t.write(txt)
            
            appuifw.app.body = t

        else :
            appuifw.app.body = appuifw.Text(txt)

    #
    #
    #
    
    def sleep (self, secs=300) :
        timer = e32.Ao_timer()
        timer.after(secs, self.friends)

        self.__timer__ = timer
        
    #
    #
    #
    
if __name__ == "__main__" :

    app = s60_twnotifier("c:\\twnotifier")
    app.loop()
