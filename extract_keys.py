import os, re, json

views_dir = r'j:\xampp\htdocs\FYP\resources\views'
ur_json_path = r'j:\xampp\htdocs\FYP\lang\ur.json'
sd_json_path = r'j:\xampp\htdocs\FYP\lang\sd.json'

with open(ur_json_path, encoding='utf-8') as f:
    ur_keys = json.load(f).keys()

with open(sd_json_path, encoding='utf-8') as f:
    sd_keys = json.load(f).keys()

extracted_keys = set()
pattern = re.compile(r"__\(['\"](.+?)['\"]\)")

for root, _, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.blade.php'):
            with open(os.path.join(root, file), encoding='utf-8') as f:
                content = f.read()
                matches = pattern.findall(content)
                for match in matches:
                    extracted_keys.add(match)

missing_ur = sorted([k for k in extracted_keys if k not in ur_keys])
missing_sd = sorted([k for k in extracted_keys if k not in sd_keys])

print('Missing in ur.json:')
for k in missing_ur:
    print(' -', k)

print('\nMissing in sd.json:')
for k in missing_sd:
    print(' -', k)
