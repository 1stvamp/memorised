# -*-python-*-

__package__    = "twsnarler.py"
__version__    = "1.0"
__author__     = "Aaron Straup Cope"
__url__        = "http://www.aaronland.info/python/twnotifier/"
__cvsversion__ = "$Revision: 1.2 $"
__date__       = "$Date: 2006/11/25 16:59:56 $"
__copyright__  = "Copyright (c) 2006 Aaron Straup Cope. Perl Artistic License."

from twnotifier import * 
from PySnarl import *
import os.path

class twsnarler (twnotifier) :

    def notify (self, tw) :

        try :
            PySnarl.snShowMessage(self.format_twitter_message(tw))
        except Exception, e :
            self.error("Failed to notify, %s" % e)
            return False

        return True

    #
    #
    #
    
if __name__ == "__main__" :

    def getHomeDir() :

        # http://mail.python.org/pipermail/python-list/2006-July/393819.html
        
        def valid(path) :
            if path and os.path.isdir(path) :
                return True
            return False
        
        def env(name) :
            return os.environ.get( name, '' )
        
        homeDir = env( 'USERPROFILE' )
        
        if not valid(homeDir) :
            homeDir = env( 'HOME' )

            if not valid(homeDir) :
                homeDir = '%s%s' % (env('HOMEDRIVE'),env('HOMEPATH'))

                if not valid(homeDir) :
                    homeDir = env( 'SYSTEMDRIVE' )

                    if homeDir and (not homeDir.endswith('\\')) :
                        homeDir += '\\'

                    if not valid(homeDir) :
                        homeDir = 'C:\\'

        return homeDir

    storeroot = getHomeDir()
    storepath = os.path.abspath(os.path.join(storeroot, u".twnotifier"))    

    app = twsnarler(storepath)
    app.loop()
