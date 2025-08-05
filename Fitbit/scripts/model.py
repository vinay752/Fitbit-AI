import mysql.connector
import os
from dotenv import load_dotenv
import vertexai
from langchain_google_vertexai import VertexAI , ChatVertexAI , VertexAIEmbeddings
from langchain.prompts import PromptTemplate
from langchain_core.messages import AIMessage
from langchain_core.output_parsers import StrOutputParser
from langchain_core.runnables import RunnableLambda



load_dotenv("../secure/.env")

PROJECT_ID = os.getenv("CLOUD_PROJECT_ID")
REGION = "us-central1"
HOST = os.getenv("HOST")
USER = os.getenv("USER")
PASSWORD = os.getenv("PASSWORD")
DB = os.getenv("DB")
TABLE = os.getenv("TABLE")


conn = mysql.connector.connect(
    host=HOST,
    user=USER,
    password=PASSWORD,
    database=DB
)
cursor = conn.cursor()

cursor.execute(f"SELECT * FROM {TABLE} LIMIT 10;")
results = cursor.fetchall()
columns = [i[0] for i in cursor.description]
print([i[0] for i in cursor.description])
print(results)

formatted_data = "\n".join([str(row) for row in results])



vertexai.init(project = PROJECT_ID , location = REGION)


# Generate summaries of text elements
def generate_text_summaries(texts, tables, summarize_texts=False):
   
   prompt_text = """You are an assistant tasked with summarizing tables and text for retrieval. \
   These summaries will be embedded and used to retrieve the raw text or table elements. \
   Give a concise summary of the table or text that is well optimized for retrieval. Table or text: {element} """
   
   prompt = PromptTemplate.from_template(prompt_text)
   empty_response = RunnableLambda(
       lambda x: AIMessage(content="Error processing document")
   )
   # Text summary chain
   model = VertexAI(
       temperature=0, model_name="gemini-pro", max_output_tokens=1024
   ).with_fallbacks([empty_response])
   summarize_chain = {"element": lambda x: x} | prompt | model | StrOutputParser()

   # Initialize empty summaries
   text_summaries = []
   table_summaries = []

   # Apply to text if texts are provided and summarization is requested
   if texts and summarize_texts:
       text_summaries = summarize_chain.batch(texts, {"max_concurrency": 1})
   elif texts:
       text_summaries = texts

   # Apply to tables if tables are provided
   if tables:
       table_summaries = summarize_chain.batch(tables, {"max_concurrency": 1})

   return text_summaries, table_summaries


# Get text summaries
text_summaries, table_summaries = generate_text_summaries(
   columns, results, summarize_texts=True
)

text_summaries[0]
