#!/usr/bin/env python
# coding: utf-8

# # 유사도 검증 

# ### package import 

# In[5]:


from sqlalchemy import create_engine
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import linear_kernel
from konlpy.tag import Okt
import pandas as pd
import numpy as np

import pymysql


# ### DB에서 data 추출

# In[19]:


db = pymysql.connect(host='localhost', user = 'root', password = 'seonga',db='national_petition' )
curs = db.cursor()

sql = "select * from petition"
curs.execute(sql)

result = curs.fetchall()

print(result)

df = pd.DataFrame(result,columns = ['code','sdays','edays','title','content','count','category','progress','link'])


# ### 청원 진행중인 게시글에 대한 분석

# In[25]:


df_pr = df[df["progress"]=='청원진행중 ']
df_pr['content'] = df_pr['content'].replace(['\n','\r','\t','-','\*','\u200b'],'',regex=True)


# In[28]:


df_pr = df_pr.reset_index(drop=True)

# 특정 청원을 기준으로 wordcloud 생성 
df_pr_count = df_pr[(df_pr['count']>= 5000)]

df_pr_count = df_pr_count.reset_index(drop=True)

df_pr_count


# ### 유사도 검정

# #### 형태소 분리

# In[29]:


from konlpy.tag import Okt

okt = Okt()

doc_nouns_list = [' '.join(okt.nouns(doc)) for doc in df_pr['content']]

print(doc_nouns_list)


# #### TF-IDF적용

# In[30]:


tfidf_vectorizer = TfidfVectorizer(min_df=2)  # 한 번만 나타나는 단어들은 무시
tfidf_matrix = tfidf_vectorizer.fit_transform(doc_nouns_list)

from sklearn.metrics.pairwise import linear_kernel
cosine_sim = linear_kernel(tfidf_matrix, tfidf_matrix)

indices = pd.Series(df_pr.index, index=df_pr['title']).drop_duplicates()


# #### 유사도를 추출하는 함수 생성 

# In[31]:


def get_recommendations(title, cosine_sim=cosine_sim):
    # 청원 타이틀로부터 해당되는 인덱스를 받아옴.
    idx = indices[title]

    # 모든 청원에 대해서 해당 청원과의 유사도 추출.
    sim_scores = list(enumerate(cosine_sim[idx]))
    
    movie_indices = [i[0] for i in sim_scores]
    movie_similarity = [i[1] for i in sim_scores]

    df_sim = pd.DataFrame({'title':df_pr['title'].iloc[movie_indices], 'sim': movie_similarity})

    return df_sim


# #### 함수 적용

# In[32]:


df_sim_total = pd.DataFrame()

for i in range(len(df_pr_count)):
    
    df_sim = get_recommendations(df_pr_count['title'][i]) # 함수에 적용 
    
    df_sim = df_sim[df_sim['sim']>=0.20] 
    
    df_end = pd.concat([df_pr.iloc[df_sim.index],df_sim[['sim']]],axis=1)
    
    df_end['link'] = df_end['code'].apply(lambda x: 'https://www1.president.go.kr/petitions/'+str(x))
    
    df_end['id'] = i
    
    df_sim_total = pd.concat([df_sim_total, df_end],axis=0)
    
    df_sim_total = df_sim_total.reset_index(drop=True)


# #### 중복되는 게시글은 유사도가 높은 것을 남김

# In[33]:


print(df_sim_total['content'].shape)
print(df_sim_total['content'].drop_duplicates().shape)

df_sim_total =  df_sim_total.sort_values(['sim'],ascending = False)
df_sim_total = df_sim_total.drop_duplicates(['content'])


# #### 유사한 문서가 5개 이상인 것들만 추출 

# In[34]:


dict_id = dict(df_sim_total['id'].value_counts()>=5).items()

id_key = []

for key, value in dict_id:                    

    if value != True:                               

        id_key.append(key) 

df_sim_total_subset = df_sim_total[~df_sim_total['id'].isin(id_key)]


# In[35]:


len(df_sim_total['id'].unique())


# In[36]:


len(df_sim_total_subset['id'].unique())


# In[37]:


len(df_sim_total_subset['id'].unique())


# In[38]:


df_sim_total_subset['category'].value_counts()


# In[39]:


df_sim_total_subset['id'].value_counts()


# #### 청원 동의 순으로 id 생성 

# In[40]:


df_sim_total_subset2 = df_sim_total_subset.groupby(['id'], as_index=False)['count'].max().sort_values(['count'], ascending = False)
df_sim_total_subset2['id_reset'] = list(range(1,len(df_sim_total_subset2)+1))

df_sim_total_subset = df_sim_total_subset.reset_index(drop=True)
df_sim_total_subset2 = df_sim_total_subset2.reset_index(drop=True)

import numpy as np

df_sim_total_subset['id_reset'] = ''

for i in range(len(df_sim_total_subset2)):
    for a in range(len(df_sim_total_subset)):
        if df_sim_total_subset['id'][a] == df_sim_total_subset2['id'][i]:
            df_sim_total_subset['id_reset'][a] = df_sim_total_subset2['id_reset'][i]


# In[41]:


del df_sim_total_subset['id']
df_sim_total_subset.rename(columns={"id_reset":"id"}, inplace = True) 


# In[42]:


df_sim_total_subset['id'].value_counts()


# #### 유사 문서들의 카테고리 분포 파악   
# 시각화 시 카테고리를 고려해야하는지를 결정하기 위한 과정 

# In[43]:


ct = pd.crosstab(df_sim_total_subset.id, df_sim_total_subset.category)

ct.plot.bar(stacked=True,figsize=(10,8))
plt.legend(title='category')

plt.title('유사 문서들의 category분포')
plt.show()


# ### 텍스트 요약

# In[44]:


from gensim.summarization.summarizer import summarize

df_sim_total_subset = df_sim_total_subset.reset_index(drop =True)

df_sim_total_subset['summary_content'] = ''


for i in range(len(df_sim_total_subset)):
    
    # 본문이 특정 개수 문장 이하일 경우 결과가 반환되지 않음.
    # 이때는 요약하지 않고 본문에서 앞 3문장을 사용함.
        
    try:
        
        df_sim_total_subset['summary_content'][i] = summarize(df_sim_total_subset['content'][i], ratio = 0.2)
        
    except ValueError:
        
        df_sim_total_subset['summary_content'][i] = None
        
    if not df_sim_total_subset['summary_content'][i]:
        
        df_sentences =  df_sim_total_subset['content'][i].split('.')

        if len(df_sentences) > 3:

            df_sim_total_subset['summary_content'][i] = '.'.join(df_sentences[:3])

        else:
            df_sim_total_subset['summary_content'][i] = '.'.join(df_sentences)


# In[45]:


df_sim_total_subset


# In[46]:


len(df_sim_total_subset)


# In[47]:


from datetime import datetime

now = datetime.now()

df_sim_total_subset['edays'] = pd.to_datetime(df_sim_total_subset['edays'],format='%Y-%m-%d')
df_sim_total_subset['ddays'] =  now- df_sim_total_subset['edays']

df_sim_total_subset['ddays'] = df_sim_total_subset['ddays'].dt.days


# In[48]:


df_sim_total_subset['edays'] = df_sim_total_subset['edays'].apply(lambda x: datetime.strftime(x, '%Y-%m-%d'))


# ### db에 저장

# In[49]:


db = pymysql.connect(host='localhost', user = 'root', password = 'seonga',db='national_petition' )
curs = db.cursor()

curs.execute("TRUNCATE TABLE petition_similarity")


# In[50]:


cols = "`,`".join([str(i) for i in df_sim_total_subset.columns.tolist()])
print(cols)

df_sim_total_subset['id'] = df_sim_total_subset['id'].astype('int')

for i,row in df_sim_total_subset.iterrows():
    query = "INSERT INTO `petition_similarity` (`" +cols + "`) VALUES (" + "%s,"*(len(row)-1) + "%s)"
    print(sql)
    print(row)
    
    curs.execute(query, tuple(row))

    db.commit()
    
db.close()


# ### 워드클라우드를 위한 DB 생성 후 데이터 적재

# In[51]:


from collections import Counter

okt = Okt()
doc_nouns_list = []

df_noun = pd.DataFrame()

stopwords = ['그것','저것','안녕하십니까','또한','대해','당시']
df_sim_total_subset['content'] = df_sim_total_subset['content'].replace(stopwords,'',regex=True)

for i in list(df_sim_total_subset['id'].unique()):
        
    df_id = df_sim_total_subset[df_sim_total_subset['id']==i]

    doc_nouns_list = [','.join(okt.nouns(doc)) for doc in df_id['content']]
    a = pd.DataFrame({'id':i, 'noun':doc_nouns_list, 'content':df_id['content'], 'count':df_id['count'], 'sim': df_id['sim']})
    
    df_noun = pd.concat([df_noun,a])


# In[52]:


df_noun


# In[53]:


res = df_noun.groupby(['id'], as_index=False)[['noun']].agg(lambda x: ', '.join(map(str, set(x))))


# In[54]:


total = pd.DataFrame()

# 한 글자 단어는 제거하기 
for i in range(len(res)):
    
    word_to = []

    for word in res['noun'][i].split(','):
        if len(word)>1:
            word_to.append(word)
    count = Counter(word_to)
    new = count.most_common(200)
            
    new = pd.DataFrame(new, columns = ['word','count'])
    id_df = pd.DataFrame({'id':list(np.repeat(res['id'][i],[len(new)]))})

    new_df = pd.concat([id_df, new],axis = 1)
    
    print(new_df)
    
    total = pd.concat([total, new_df],axis = 0)


# ### db에 저장 

# In[55]:


db = pymysql.connect(host='localhost', user = 'root', password = 'seonga',db='national_petition' )
curs = db.cursor()
cols = "`,`".join([str(i) for i in total.columns.tolist()])

curs.execute("TRUNCATE TABLE petition_wordcloud") # table 의 모든 row 제거 

for i,row in total.iterrows():
    query = "INSERT INTO `petition_wordcloud` (`" +cols + "`) VALUES (" + "%s,"*(len(row)-1) + "%s)"
    
    curs.execute(query, tuple(row))

    db.commit()
    
db.close()

