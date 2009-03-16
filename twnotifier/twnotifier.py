# -*-python-*-

__package__    = "twnotifier.py"
__version__    = "1.0"
__author__     = "Aaron Straup Cope"
__url__        = "http://www.aaronland.info/python/twnotifier/"
__cvsversion__ = "$Revision: 1.2 $"
__date__       = "$Date: 2006/11/25 16:59:56 $"
__copyright__  = "Copyright (c) 2006 Aaron Straup Cope. Perl Artistic License."

import sys
import time
import anydbm
import re
import base64
import httplib
import urllib
import os
import pwd
import posix

class twnotifier :

    def __init__ (self, storepath) :

        self.__storepath__ = storepath
        self.__store__  = None

        self.__msg__  = u""
        self.__kill__ = False
        self.__auth__ = None
        
        try :
            import elementtree
        except Exception, e :
            self.error("Failed to load py-elementtree, %s" % e)

    #
    #
    #
    
    def __del__ (self) :

        try :
            self.__store__.close()
        except Exception, e :
            self.error("Failed to close store, %s" % e)

    #
    #
    #
        
    def setup (self) :

        if not self.setup_store() :
            return False

        if not self.setup_notification_agent() :
            return False

        if not self.test_connection() :
            return False

        if not self.test_credentials() :
            return False
                
        return True
    
    #
    #
    #

    def setup_store (self) :

        try :
            self.__store__ = anydbm.open(self.__storepath__, "c")
        except Exception, e:
            self.error("Failed to open storage object, %s" % e)
            return False

        return True
    
    #
    #
    #
    
    def setup_notification_agent(self) :
        return True
        
    #
    #
    #
    
    def loop (self) :
        if self.setup() :
            self.run()
        else :
            self.error("Setup failed. Stopping")
            
    #
    #
    #

    def run (self) :
        self.friends()
                
    #
    #
    #
    
    def friends (self) :

        if self.__kill__ :
            return False
        
        xml = self.execute_twitter_request("/statuses/friends.xml")

        if not xml :
            self.error("Failed to fetch friends status")
        else :
        
            res = self.parse_friends_status(xml)
            tw  = {}
        
            for m in res :

                uid = m[2]

                if self.__store__.has_key(uid) :
                    continue

                tw[int(uid)] = {'who':m[1], 'what':m[3], 'when':m[4], 'looks':self.download_image(m[0])}

            #

            if len(tw) :

                ids = tw.keys()
                ids.sort()
                ids.reverse()
                
                self.pre_notify()
            
                for id in ids :
                    # self.log("notify with %s" % id)

                    if self.notify(tw[id]) :
                        self.__store__[str(id)] = "1"

                self.post_notify()
            
            else :
                self.log("no updates as of %s" % time.ctime(time.time()))

        #

        self.sleep()

    #
    #
    #

    def parse_friends_status (self, xml) :
        
        matches = []
        
        import elementtree.ElementTree as ET
        tree  = ET.fromstring(xml)
        
        for users in tree.getiterator("users") :
            for user in users.getiterator("user") :
                name = user.findtext("name")
                image_url = user.findtext("profile_image_url")
                for status in user.getiterator("status") :
                    matches.append((
                        image_url,
                        name,
                        status.findtext("id"),
                        status.findtext("text"),
                        status.findtext("relative_created_at")
                    ))
        
        matches.reverse()
        return matches
    
    #
    #
    #
    
    def sleep (self, secs=300) :
        time.sleep(secs)
        self.friends()
        
    #
    #
    #
    
    def notify (self, tw) :
        self.log(tw)

    #
    #
    #
    
    def pre_notify (self) :
        pass

    #
    #
    #
    
    def post_notify (self) :
        pass

    #
    #
    #
    
    def format_twitter_message (self, tw) :
        return "%s -- %s" % (tw['what'], tw['when'])
    
    #
    #
    #
    
    def execute_twitter_request (self, uri, params=None) :

        headers =  self.get_twitter_auth()
        conn    = httplib.HTTPConnection("twitter.com")

        try :
            conn.request('GET', uri, params, headers)
        except Exception, e:
            return None

        try :
            res = conn.getresponse()
            return res.read()
        except Exception, e:
            return None

    #
    #
    #

    def get_twitter_auth (self) :

        if self.__auth__ :
            return self.__auth__
        
        user = self.__store__['user']
        pswd = self.__store__['pswd']
        auth = base64.encodestring(user + ":" + pswd)
        
        self.__auth__ = {"Authorization":"Basic %s" % auth} 
        return self.__auth__
    
    #
    #
    #

    def get_credentials (self) :

        user = None
        pswd = None
        
        if not self.__store__.has_key("user") :
            user = self.prompt(u"your twitter username", "text")

            if user == None :
                return False

            self.__store__["user"] = user
        
        if not self.__store__.has_key("pswd") :
            pswd = self.prompt(u"your twitter pswd", "text")

            if pswd == None :
                return False

            self.__store__["pswd"] = pswd

        return True

    #
    #
    #
    
    def prompt (self, query, type) :
        return raw_input("%s : " % query)
    
    #
    #
    #
    
    def test_credentials (self) :
        self.log("Testing your twitter login")

        for item in ('user', 'pswd') :
            if not self.__store__.has_key(item) :

                if not self.get_credentials() :
                    self.error("Unable to get twitter login")
                    return False

        #
        #
        #

        method = 'HEAD'

        if sys.platform=='symbian_s60' :
            method = 'GET'
        
        headers =  self.get_twitter_auth()
        conn    = httplib.HTTPConnection("twitter.com")

        try :
            conn.request(method, '/statuses/friends.xml', None, headers)
        except Exception, e:
            self.log("Unable to connect to test auth, %s" % e)            
            return False

        try :
            res = conn.getresponse()
        except Exception, e :
            self.error("Unable to read auth response : %s" % e)
            return False
        
        if res.status != 200 :
            self.error("Auth failed with status %s" % r.status)
            return False

        return True

    #
    #
    #
    
    def download_image (self, iurl):
        i = iurl.rfind('/')
        d = iurl.rfind('?')
        user = pwd.getpwuid(posix.geteuid())
        storeroot = os.path.abspath(user[5])
        filed = storeroot + '/twitter-icons/' + iurl[i+1:d]
        if not os.path.isfile(filed):
            try:
                f = urllib.urlretrieve(iurl, filed)
            except Exception, e :
                self.error("Failed to download picture, %s" % e)
        return filed

    def test_connection (self) :
        self.log("Testing your network connection")

        method = 'HEAD'

        if sys.platform=='symbian_s60' :
            method = 'GET'

        conn = httplib.HTTPConnection('twitter.com')
            
        try :
            conn.request(method, '/')
        except Exception, e :
            self.error("Can not find the network love (%s) :-(" % e)
            return False

        try :
            res = conn.getresponse()
        except Exception, e :
            self.log("Unable to test network response, %s" % e)
            return False
        
        return True

    #
    #
    #

    def clear_log (self) :
        self.__msg__ = u""

    #
    #
    #

    def error(self, msg) :
        self.log(msg)

    #
    #
    #
    
    def log (self, msg, error=0) :
        print msg

    #
    #
    #
    
if __name__ == "__main__" :

    storepath = raw_input("Where should I store cached twitters? ")
    
    app = twnotifier(storepath)
    app.loop()