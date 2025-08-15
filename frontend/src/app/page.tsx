import LoginForm from './components/LoginForm';

export default function Home() {
  return (
    <main className="flex min-h-screen flex-col items-center justify-center p-24">
      <div className="w-full max-w-md">
        <h1 className="text-2xl font-bold text-center mb-8">Welcome</h1>
        <p className="text-center text-gray-600 mb-8">
          Sign in with your Microsoft account to continue
        </p>
        <LoginForm />
      </div>
    </main>
  );
}
