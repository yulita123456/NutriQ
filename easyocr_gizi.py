import sys
import easyocr
import json

if len(sys.argv) < 2:
    print(json.dumps({"error": "No image path provided"}))
    exit(1)

img_path = sys.argv[1]
reader = easyocr.Reader(['id', 'en'], gpu=False)
results = reader.readtext(img_path, detail=0)

print(json.dumps({"results": results}, ensure_ascii=False))
