import os
import re

# Define the docs directory path
plugin_path = os.getcwd()
docs_directory = os.path.join(plugin_path, 'docs')

# Create the docs directory if it doesn't exist
if not os.path.exists(docs_directory):
    os.makedirs(docs_directory)

# Custom directories to exclude
excluded_dirs = ['node_modules', 'vendor', '.git', '.idea', 'docs', 'build', 'languages', 'review-images', 'partials', 'backup', 'directory_structure.py']  # Add/remove as needed
excluded_files = ['README.md', 'config.php', 'webpack-development.config.js', 'webpack-production.config.js']
excluded_extensions = ['.txt', '.log', '.md', '.json', '.py']                       # Add/Remove as needed

def create_directory_structure(path):
    directory_structure = {}
    
    for dirpath, dirnames, filenames in os.walk(path):
        # Filter directories
        dirnames[:] = [d for d in dirnames if not d.startswith('.') and d not in excluded_dirs]
        
        # Filter files by name and extension
        filtered_files = []
        for f in filenames:
            if f.startswith('.'):
                continue
            if f in excluded_files:
                continue
            ext = os.path.splitext(f)[1].lower()
            if ext in excluded_extensions:
                continue
            filtered_files.append(f)
        
        filenames = filtered_files

        # Sort for consistent output
        dirnames.sort()
        filenames.sort()

        rel_dir = os.path.relpath(dirpath, path)
        if rel_dir == '.':
            rel_dir = ''

        directory_structure[rel_dir] = filenames

    return directory_structure

def clean_php_content(content):
    # Remove single-line comments
    content = re.sub(r'//.*', '', content)
    # Remove multi-line comments
    content = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)
    return content

def extract_php_details(file_content):
    cleaned_content = clean_php_content(file_content)
    classes = re.findall(r'class\s+(\w+)', cleaned_content)
    functions = re.findall(r'function\s+(\w+)\s*\(([^)]*)\)', cleaned_content)
    
    details = {
        'classes': classes,
        'functions': [func[0] for func in functions]
    }
    return details

def build_nested_structure(directory_tree):
    root = {'files': [], 'dirs': {}}

    for rel_dir, files in directory_tree.items():
        parts = rel_dir.split(os.sep) if rel_dir else []
        current = root
        for p in parts:
            if p not in current['dirs']:
                current['dirs'][p] = {'files': [], 'dirs': {}}
            current = current['dirs'][p]
        current['files'] = files

    return root

def print_structure_recursive(node, path, plugin_path, indent=0):
    lines = []
    indent_str = ' ' * 4 * indent  # 4 spaces per indent level

    # Print files first
    for f in node['files']:
        if f.endswith('.php'):
            lines.append(f"{indent_str}- **{f}**")
            file_path = os.path.join(plugin_path, path, f) if path else os.path.join(plugin_path, f)
            with open(file_path, 'r', encoding='utf-8') as php_file:
                content = php_file.read()
                details = extract_php_details(content)
                if details['classes']:
                    lines.append(f"{indent_str}    - **Classes:**")
                    for cls in details['classes']:
                        lines.append(f"{indent_str}        - {cls}")
                if details['functions']:
                    lines.append(f"{indent_str}    - **Functions:**")
                    for func in details['functions']:
                        lines.append(f"{indent_str}        - {func}")
        else:
            lines.append(f"{indent_str}- **{f}**")

    # Print directories next
    for d, sub_node in node['dirs'].items():
        lines.append(f"{indent_str}- **{d}/**")
        sub_path = os.path.join(path, d) if path else d
        lines.extend(print_structure_recursive(sub_node, sub_path, plugin_path, indent+1))

    return lines

# MAIN EXECUTION

directory_tree = create_directory_structure(plugin_path)
nested_structure = build_nested_structure(directory_tree)

output_md_file = os.path.join(docs_directory, 'combined_structure.md')
with open(output_md_file, 'w') as f:
    markdown_lines = print_structure_recursive(nested_structure, '', plugin_path)
    f.write('\n'.join(markdown_lines))

print(f"Combined structure written to {output_md_file}")