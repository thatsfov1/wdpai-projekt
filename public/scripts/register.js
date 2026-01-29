const roleInputs = document.querySelectorAll('input[name="role"]');
const workerFields = document.getElementById('workerFields');

const toggleWorkerFields = () => {
    const selectedRole = document.querySelector('input[name="role"]:checked').value;
    workerFields.style.display = selectedRole === 'worker' ? 'block' : 'none';
}

for (let i = 0; i < roleInputs.length; i++) {
    roleInputs[i].addEventListener('change', toggleWorkerFields);
}

toggleWorkerFields();
