// validation/form-validation.js
// Universal AJAX form validation for all forms on the site
// - Uses existing HTML5 validation rules
// - Provides live feedback as user types
// - Green border for valid, red for invalid
// - Handles all forms automatically

(function() {

  // Utility: add feedback border and message
  function setFieldState(field, valid, message) {
    if (valid) {
      field.style.borderColor = 'green';
      removeErrorMessage(field);
    } else {
      field.style.borderColor = 'red';
      showErrorMessage(field, message);
    }
  }

  // Utility: clear feedback border and message
  function clearFieldState(field) {
    field.style.borderColor = '';
    removeErrorMessage(field);
  }

  // Show error message below the field
  function showErrorMessage(field, message) {
    removeErrorMessage(field);
    if (!message) return;
    const msg = document.createElement('div');
    msg.className = 'input-feedback';
    msg.style.color = 'red';
    msg.textContent = message;
    field.insertAdjacentElement('afterend', msg);
  }

  // Remove error message if present
  function removeErrorMessage(field) {
    if (field.nextElementSibling && field.nextElementSibling.classList.contains('input-feedback')) {
      field.nextElementSibling.remove();
    }
  }

  // Get the validation message for a field, matching server-side rules
  function getValidationMessage(field) {
    // Required
    if (field.validity.valueMissing) return 'This field is required.';

    // Email (match PHP: FILTER_VALIDATE_EMAIL)
    if (field.type === 'email' || field.name.toLowerCase().includes('email')) {
      // PHP's FILTER_VALIDATE_EMAIL is roughly:
      // /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i
      const emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
      if (field.value && !emailPattern.test(field.value)) {
        return 'Please enter a valid email address.';
      }
    }

    // Password (match PHP server-side)
    if (field.type === 'password' || field.name.toLowerCase().includes('password')) {
      const val = field.value || '';
      const errors = [];
      if (val.length < 8) errors.push('at least 8 characters');
      if (!/[A-Z]/.test(val)) errors.push('one uppercase letter');
      if (!/[a-z]/.test(val)) errors.push('one lowercase letter');
      if (!/[0-9]/.test(val)) errors.push('one number');
      // If you want to require a symbol, uncomment below:
      // if (!/[^A-Za-z0-9]/.test(val)) errors.push('one symbol');
      if (errors.length > 0) {
        return 'Password must contain: ' + errors.join(', ') + '.';
      }
    }

    // Confirm password
    if (field.name && field.name.toLowerCase().includes('confirm')) {
      const form = field.form;
      if (form) {
        const pw = form.querySelector('input[type="password"][name]:not([name*="confirm"])');
        if (pw && field.value !== pw.value) {
          return 'Passwords do not match.';
        }
      }
    }

    // HTML5 built-in
    if (field.validity.typeMismatch) {
      if (field.type === 'url') return 'Please enter a valid URL.';
      return 'Please enter a valid value.';
    }
    if (field.validity.tooShort) return `Please lengthen to at least ${field.minLength} characters.`;
    if (field.validity.tooLong) return `Please shorten to no more than ${field.maxLength} characters.`;
    if (field.validity.patternMismatch) return 'Please match the requested format.';
    if (field.validity.rangeUnderflow) return `Value must be at least ${field.min}.`;
    if (field.validity.rangeOverflow) return `Value must be at most ${field.max}.`;
    if (field.validity.stepMismatch) return 'Please enter a valid value.';
    return field.validationMessage || 'Invalid input.';
  }

  // Validate a single field
  function validateField(field) {
    if (field.disabled || field.type === 'hidden' || field.type === 'submit' || field.type === 'button') return true;
    if (field.willValidate === false) return true;
    if (field.value === '' && !field.required) {
      clearFieldState(field);
      return true;
    }

    // Custom password validation: override HTML5 validity for password fields
    if (field.type === 'password' || field.name.toLowerCase().includes('password')) {
      const val = field.value || '';
      const errors = [];
      if (val.length < 8) errors.push('at least 8 characters');
      if (!/[A-Z]/.test(val)) errors.push('one uppercase letter');
      if (!/[a-z]/.test(val)) errors.push('one lowercase letter');
      if (!/[0-9]/.test(val)) errors.push('one number');
      // If you want to require a symbol, uncomment below:
      // if (!/[^A-Za-z0-9]/.test(val)) errors.push('one symbol');
      if (errors.length > 0) {
        setFieldState(field, false, 'Password must contain: ' + errors.join(', ') + '.');
        return false;
      }
    }

    // Custom confirm password validation
    if (field.name && field.name.toLowerCase().includes('confirm')) {
      const form = field.form;
      if (form) {
        const pw = form.querySelector('input[type="password"][name]:not([name*="confirm"])');
        if (pw && field.value !== pw.value) {
          setFieldState(field, false, 'Passwords do not match.');
          return false;
        }
      }
    }

    // Email validation override
    if (field.type === 'email' || field.name.toLowerCase().includes('email')) {
      const emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
      if (field.value && !emailPattern.test(field.value)) {
        setFieldState(field, false, 'Please enter a valid email address.');
        return false;
      }
    }

    // Default HTML5 validation
    const valid = field.checkValidity();
    setFieldState(field, valid, valid ? '' : getValidationMessage(field));
    return valid;
  }

  // Validate all fields in a form
  function validateForm(form) {
    let valid = true;
    Array.from(form.elements).forEach(field => {
      if (!validateField(field)) valid = false;
    });
    return valid;
  }

  // Attach listeners to all forms
  function setupValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
      // Validate on input
      form.addEventListener('input', function(e) {
        if (e.target && e.target.form === form) {
          validateField(e.target);
        }
      });
      // Validate on blur
      form.addEventListener('blur', function(e) {
        if (e.target && e.target.form === form) {
          validateField(e.target);
        }
      }, true);
      // Validate all on submit
      form.addEventListener('submit', function(e) {
        if (!validateForm(form)) {
          e.preventDefault();
        }
      });
    });
  }

  // Run on DOMContentLoaded
  document.addEventListener('DOMContentLoaded', setupValidation);
})();
