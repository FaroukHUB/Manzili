import NextAuth from "next-auth"
import Credentials from "next-auth/providers/credentials"

export const { handlers, auth, signIn, signOut } = NextAuth({
  providers: [
    Credentials({
      credentials: {
        password: { label: "Mot de passe", type: "password" },
      },
      authorize: async (credentials) => {
        const appPassword = process.env.APP_PASSWORD
        if (!appPassword || credentials?.password !== appPassword) {
          return null
        }
        return {
          id: "user_1",
          name: "Moi",
          email: "me@app.local",
        }
      },
    }),
  ],
  pages: {
    signIn: "/login",
  },
  callbacks: {
    authorized({ auth }) {
      return !!auth?.user
    },
  },
  session: {
    strategy: "jwt",
  },
})
