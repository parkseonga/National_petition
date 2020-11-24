from bs4 import BeautifulSoup
import requests
from selenium import webdriver

from urllib.request import urlopen, Request
from time import sleep

import pymysql
import re

# selenium으로 가져오지 않으면 원하는 링크를 추출할 수 없음.
driver = webdriver.Chrome(r'C:\Users\a0105\Desktop\크롤링코드\chromedriver_win32\chromedriver.exe')

total_link = []


for i in range(1,5): # 과거 데이터를 크롤링 할 때는 1~ 112까지 돌렸었음. 
    url = 'https://www1.president.go.kr/petitions/?c=0&only=0&page='+str(i)+'&order=0'
    
    print(url)
    
    driver.get(url)

    sleep(3)
    
    a = driver.page_source

    bsObj = BeautifulSoup(a, 'html.parser')
    
    code_link = bsObj.find_all("a", {'class':"cb relpy_w"}) 

    for a_tag in code_link :
        total_link.append(a_tag["href"])
        
driver.close()


# 중복제거하여 링크 가져옴
total=list(set(total_link))

db = pymysql.connect(host='localhost', user = 'root', password = 'seonga',db='national_petition' )
curs = db.cursor()

for link in total: 

    url = 'https://www1.president.go.kr'+link
    sleep(1.1)
    print(url)

    a = requests.get(url)


    bsObj = BeautifulSoup(a.text, 'html.parser')
    
    try:
        progress = bsObj.find("div",{"class":"petitionsView_progress"}).get_text()
        title = bsObj.find("h3",{"class":"petitionsView_title"}).get_text()
        count = bsObj.find("h2",{"class":"petitionsView_count"}).get_text()
        content = bsObj.find("div",{"class":"View_write"}).get_text()
        info_list = bsObj.find("ul",{"class":"petitionsView_info_list"}).get_text()

        info_list = info_list.split("\n")
        category = info_list[1]
        sdays = info_list[2]
        edays = info_list[3]

        code = str(link[11:])
        days = str(info_list[4:])
        progress = str(progress[1:])
        title = str(title)
        count = str([int (i) for i in re.findall('\d+',str(count.replace(',','')))][0])  # 숫자만 추출
        content = str(content)
        category = str(category[4:])
        sdays = str(sdays[4:])
        edays = str(edays[4:])
        
    except:
        pass
    
    query = "INSERT IGNORE INTO petition(code, sdays, edays, title, content, count, category, progress) VALUES(%s,%s,%s,%s,%s,%s,%s,%s)"

    print(query)

    curs.execute(query,(code,sdays,edays,title,content,count,category,progress))
    print(title)
        
db.commit()    
db.close()
