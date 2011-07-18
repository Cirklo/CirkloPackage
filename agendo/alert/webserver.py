#Copyright Jon Berg , turtlemeat.com


import os
import shutil
import string,cgi,time
import subprocess
import sys
from BaseHTTPServer import BaseHTTPRequestHandler, HTTPServer
#import pri

class MyHandler(BaseHTTPRequestHandler):

    def do_GET(self):
        try:
                print self.client_address[0]
                if self.client_address[0]!='127.0.0.1':
                    return
                if self.path[0:12]=='/favicon.ico':
                    return
                path=os.path.normpath("c:\windows\system32\cmd.exe")
                #print self.path[0:12]
                phonePos=self.path.rfind('phone=')
                msgPos=self.path.rfind('msg=')
                phone=self.path[phonePos+6:msgPos-1]
                msg=self.path[msgPos+4:]
                self.send_response(200)
                self.send_header('Content-type','text/html')
                self.end_headers()
                msg=msg.replace("%20"," ")
                a1="echo " + msg + " | d:\\www\\cal\\alert\gnokii --config d:\\www\cal\\alert\\gnokiirc --sendsms " + phone
                print a1
                args=("/c",a1) 
                subprocess.call([path, args],shell=False)
                #echo "this is a test" | gnokii --config gnokiirc --sendsms 936449
                #self.wfile.write(f.read())
                #f.close()
                return
        except IOError:
            self.send_error(404,'File Not Found: %s' % self.path)


def main():
    try:
        server = HTTPServer(('', 8888), MyHandler)
        print 'started httpserver...'
        server.serve_forever()
    except KeyboardInterrupt:
        print '^C received, shutting down server'
        server.socket.close()

if __name__ == '__main__':
    main()

