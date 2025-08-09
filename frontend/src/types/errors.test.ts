import { NetworkError, PermissionError, AuthenticationError } from './errors';

describe('Custom Error Classes', () => {
  describe('NetworkError', () => {
    it('should create a NetworkError with correct name and message', () => {
      const errorMessage = 'Network connection failed';
      const error = new NetworkError(errorMessage);

      expect(error).toBeInstanceOf(NetworkError);
      expect(error).toBeInstanceOf(Error);
      expect(error.name).toBe('NetworkError');
      expect(error.message).toBe(errorMessage);
    });

    it('should have a stack trace', () => {
      const error = new NetworkError('Test error');
      expect(error.stack).toBeDefined();
    });
  });

  describe('PermissionError', () => {
    it('should create a PermissionError with correct name and message', () => {
      const errorMessage = 'Access denied to resource';
      const error = new PermissionError(errorMessage);

      expect(error).toBeInstanceOf(PermissionError);
      expect(error).toBeInstanceOf(Error);
      expect(error.name).toBe('PermissionError');
      expect(error.message).toBe(errorMessage);
    });

    it('should have a stack trace', () => {
      const error = new PermissionError('Test error');
      expect(error.stack).toBeDefined();
    });
  });

  describe('AuthenticationError', () => {
    it('should create an AuthenticationError with correct name and message', () => {
      const errorMessage = 'Authentication failed';
      const error = new AuthenticationError(errorMessage);

      expect(error).toBeInstanceOf(AuthenticationError);
      expect(error).toBeInstanceOf(Error);
      expect(error.name).toBe('AuthenticationError');
      expect(error.message).toBe(errorMessage);
    });

    it('should have a stack trace', () => {
      const error = new AuthenticationError('Test error');
      expect(error.stack).toBeDefined();
    });
  });

  describe('Error inheritance', () => {
    it('should be catchable as generic Error', () => {
      const errors = [
        new NetworkError('Network error'),
        new PermissionError('Permission error'),
        new AuthenticationError('Auth error')
      ];

      errors.forEach(error => {
        try {
          throw error;
        } catch (e) {
          expect(e).toBeInstanceOf(Error);
        }
      });
    });

    it('should be distinguishable by type', () => {
      const networkError = new NetworkError('Network error');
      const permissionError = new PermissionError('Permission error');
      const authError = new AuthenticationError('Auth error');

      expect(networkError).not.toBeInstanceOf(PermissionError);
      expect(networkError).not.toBeInstanceOf(AuthenticationError);

      expect(permissionError).not.toBeInstanceOf(NetworkError);
      expect(permissionError).not.toBeInstanceOf(AuthenticationError);

      expect(authError).not.toBeInstanceOf(NetworkError);
      expect(authError).not.toBeInstanceOf(PermissionError);
    });
  });
});