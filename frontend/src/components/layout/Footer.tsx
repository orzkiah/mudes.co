export function Footer() {
  return (
    <footer className="md:ml-[240px] bg-surface-container-lowest border-t border-outline-variant py-4 px-4 md:px-6 flex flex-col md:flex-row justify-between items-center text-on-surface-variant text-label-sm gap-2">
      <p>© {new Date().getFullYear()} Mudes.co Admin Portal.</p>
      <div className="flex gap-4">
        <a href="#" className="hover:text-primary">Privacy Policy</a>
        <a href="#" className="hover:text-primary">Terms of Service</a>
        <a href="#" className="hover:text-primary">Help Center</a>
      </div>
    </footer>
  );
}
