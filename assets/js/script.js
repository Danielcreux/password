document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Copy password to clipboard
    document.querySelectorAll('.copy-password').forEach(button => {
        button.addEventListener('click', function() {
            const password = this.getAttribute('data-password');
            navigator.clipboard.writeText(password).then(() => {
                const originalIcon = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i>';
                
                setTimeout(() => {
                    this.innerHTML = originalIcon;
                }, 2000);
            });
        });
    });
    
    // Generate random password
    document.querySelectorAll('.generate-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const password = generatePassword(12);
            input.value = password;
            
            // Trigger input event for any potential validations
            input.dispatchEvent(new Event('input'));
        });
    });
    
    // Function to generate random password
    function generatePassword(length) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
        let password = '';
        
        for (let i = 0; i < length; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        return password;
    }
    
    // Close alert messages after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);
    
    // Eliminar aplicación con confirmación (caracteres corregidos)
    document.querySelectorAll('.delete-app').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const appId = this.getAttribute('data-id');
            
            if (confirm('¿Estás seguro de que quieres eliminar esta aplicación? Esta acción no se puede deshacer.')) {
                window.location.href = `departments.php?action=delete_app&id=${appId}`;
            }
        });
    });

    // Eliminar departamento (caracteres corregidos)
    document.querySelectorAll('.delete-dept').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const deptId = this.getAttribute('data-id');
            
            if (confirm('¿Estás seguro de que deseas eliminar este departamento? Solo se puede eliminar si no tiene aplicaciones asociadas.')) {
                window.location.href = `departments.php?action=delete_dept&id=${deptId}`;
            }
        });
    });
});
