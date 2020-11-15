#!/usr/bin/python
# -*- coding:utf8 -*-
#Author:Ettack
#Email:ettack#gmail.com

'''这个脚本利用类似与multiccl的原理来定位webshell中的特征码，使用时需和
待定位的webshell放于同一目录中运行，运行后产生TempDir_Dog文件夹，使用
安全狗一类软件查杀产生的文件，然后再回车继续脚本，如此反复，直到找出足够
多的特征码，手动修改后即可达到免杀效果'''

import sys, os
from shutil import copy2,move,rmtree
if sys.getdefaultencoding() != 'utf-8':
    reload(sys)
    sys.setdefaultencoding('utf-8')

DIR = "TempDir_Dog"

def linecount(filename):
    '''返回文件的行数'''
    fp = open(filename)
    lc = sum(1 for line in fp)
    fp.close()
    return lc

def cutfile(infile,outfile,lines):
    '''割除文件的前<lines>行'''
    fp=open(infile,'r')
    allLines = fp.readlines()
    fp.close()
    allLines = allLines[lines:]
    fp=open(outfile,'w')
    fp.writelines(allLines)
    fp.close()

def outFileName(inFileName,number):
    '''返回切片文件的文件名，例如003_webshell.asp'''
    if len(str(linecount(inFileName))) < 4:
        ofname = "%03d_%s" %(number,inFileName)
    else:
        ofname = "%04d_%s" %(number,inFileName)
    return ofname

def genFiles(inFile):
    '''产生分别割除1行、2行、3行……的切片文件'''
    for i in range(linecount(inFile)):
        cutfile(inFile,outFileName(inFile,i),i)

def reWriteFile(inFile,lines):
    '''重写文件，将已经定位的特征码替换为特定字串'''
    with open(inFile,'r+') as fp:
        allLines = fp.readlines()
        for l in lines:
            allLines[l] = "****************  This line contains defination codes!  *******************\r\n"
        fp.seek(0)
        fp.writelines(allLines)
        fp.close()

def checkFiles(inFile):
    '''检查查杀后剩余文件，返回定位出的特征码行数'''
    if os.path.exists(outFileName(inFile,0)):
        print "特征码定位完毕!"
        return 0
    for i in range(linecount(inFile)):
        if os.path.exists(outFileName(inFile,i)):
            print u"发现第%d行中含有特征码!" %(i)
            break
    return i-1

def doByPass(inFile):
    '''组织进行免杀流程的函数'''
    lines = []
    tempMove = '..'+os.sep+inFile+'.temp'
    while True:
        genFiles(inFile)
        move(inFile,tempMove)
#       raw_input("Use safedog to scan files,then come back and press ENTER to continue:")
        raw_input(u"使用安全狗查杀产生的文件，结束后按回车键继续......")
        move(tempMove,inFile)
        defLine = checkFiles(inFile)
        if defLine == 0:
            os.chdir("..")
            rmtree(DIR)
            j = 0
            for i in lines:
                lines[j] = i+1
                j=j+1
            print u"\n在第%s行发现特征码，要免杀需手动修改这些行的代码" %(str(lines))[1:-1]
            break
        else:
            lines.append(defLine)
            reWriteFile(inFile,lines)

def main():
    file2bypass = sys.argv[1]
    os.mkdir(DIR)
    copy2(file2bypass,DIR)
    os.chdir(DIR)
    print '\n%s has %d lines\n' % (file2bypass,linecount(file2bypass))
    doByPass(file2bypass)

if __name__=="__main__":
	main()






