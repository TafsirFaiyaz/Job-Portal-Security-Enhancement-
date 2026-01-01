import java.awt.*;
import java.awt.event.*;
import javax.swing.*;

public class ColorChanger extends JFrame {
    private JPanel panel;
    private JLabel label;
    private JButton redButton;
    private JButton blueButton;
    private JButton yellowButton; 

    public ColorChanger() {
        setTitle("Color Changer");
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLayout(new BorderLayout());

        panel = new JPanel();
        panel.setBackground(Color.WHITE);

        label = new JLabel("Click a button to change colors!");
        label.setFont(new Font("Arial", Font.BOLD, 16));
        label.setForeground(Color.BLACK);

        redButton = new JButton("Red");
        blueButton = new JButton("Blue");
        yellowButton = new JButton("Yellow");

        // Register event listeners
        redButton.addActionListener(new RedButtonListener());
        blueButton.addActionListener(new BlueButtonListener());
        yellowButton.addActionListener(new YellowButtonListener()); 

        // Add buttons to their own panel
        JPanel buttonPanel = new JPanel();
        buttonPanel.add(redButton);
        buttonPanel.add(blueButton);
        buttonPanel.add(yellowButton);

        // Add components to the main frame
        panel.add(label);
        add(panel, BorderLayout.CENTER);
        add(buttonPanel, BorderLayout.SOUTH);

        setSize(400, 200);
        setVisible(true);
    }

    // Listener for Red Button
    private class RedButtonListener implements ActionListener {
        public void actionPerformed(ActionEvent e) {
            panel.setBackground(Color.RED);
            label.setForeground(Color.WHITE);
        }
    }

    // Listener for Blue Button
    private class BlueButtonListener implements ActionListener {
        public void actionPerformed(ActionEvent e) {
            panel.setBackground(Color.BLUE);
            label.setForeground(Color.WHITE); 
        }
    }

    // Listener for Yellow Button
    private class YellowButtonListener implements ActionListener {
        public void actionPerformed(ActionEvent e) {
            panel.setBackground(Color.YELLOW);
            label.setForeground(Color.BLACK); 
        }
    }

    public static void main(String[] args) {
        new ColorChanger();
    }
}