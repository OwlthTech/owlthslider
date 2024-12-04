import os
import re

# Define the docs directory path
plugin_path = os.getcwd()
docs_directory = os.path.join(plugin_path, 'docs')

# Create the docs directory if it doesn't exist
if not os.path.exists(docs_directory):
    os.makedirs(docs_directory)

# Function to create the directory structure
def create_directory_structure(path):
    directory_structure = {}
    
    for dirpath, dirnames, filenames in os.walk(path):
        rel_dir = os.path.relpath(dirpath, path)
        if rel_dir == '.':
            rel_dir = ''
        directory_structure[rel_dir] = filenames

    return directory_structure

# Function to write the directory structure to markdown
def write_directory_structure_to_md(directory_tree, output_file):
    with open(output_file, 'w') as f:
        for folder, files in directory_tree.items():
            if folder:
                f.write(f"### {folder}/\n")
            else:
                f.write(f"### Root/\n")
            for file in files:
                link = file.replace(' ', '%20')
                file_id = file.replace('.', '_').replace(' ', '_').lower()
                f.write(f"  - [{file}](#{file_id})\n")

# Function to clean and extract PHP content
def clean_php_content(content):
    content = re.sub(r'//.*', '', content)
    content = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)
    return content

def extract_php_details(file_content):
    cleaned_content = clean_php_content(file_content)
    classes = re.findall(r'class\s+(\w+)', cleaned_content)
    functions = re.findall(r'function\s+(\w+)\s*\(([^)]*)\)', cleaned_content)
    
    details = {
        'classes': classes,
        'functions': [{'name': func[0], 'params': func[1]} for func in functions]
    }
    return details

# Function to write file details to markdown
def write_file_details_to_md(directory_tree, output_file, plugin_path):
    with open(output_file, 'a') as f:
        for folder, files in directory_tree.items():
            for file in files:
                if file.endswith('.php'):
                    file_path = os.path.join(plugin_path, folder, file)
                    with open(file_path, 'r', encoding='utf-8') as php_file:
                        content = php_file.read()
                        details = extract_php_details(content)
                        file_id = file.replace('.', '_').replace(' ', '_').lower()
                        f.write(f"\n\n## {file} <a id=\"{file_id}\"></a>\n")
                        if details['classes']:
                            f.write("### Classes\n")
                            for cls in details['classes']:
                                f.write(f"- {cls}\n")
                        if details['functions']:
                            f.write("### Functions\n")
                            for func in details['functions']:
                                f.write(f"- **{func['name']}**\n")
                                # Only write the parameters if they are valid and meaningful
                                if func['params'] and func['params'].strip() and func['params'].strip() != '':
                                    f.write(f"  - Parameters: {func['params']}\n")

# Generate the directory structure
directory_tree = create_directory_structure(plugin_path)

# Define the output markdown files in the docs directory
output_md_structure_file = os.path.join(docs_directory, 'directory_structure.md')
output_md_details_file = os.path.join(docs_directory, 'file_details.md')

# Write the directory structure and file details to the docs directory
write_directory_structure_to_md(directory_tree, output_md_structure_file)
write_file_details_to_md(directory_tree, output_md_details_file, plugin_path)

print(f"Directory structure written to {output_md_structure_file}")
print(f"File details written to {output_md_details_file}")